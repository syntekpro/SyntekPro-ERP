const dbName = 'syntekpro-pos';
const dbVersion = 1;

const state = {
    bootstrap: readBootstrap(),
    products: [],
    queue: [],
    cart: new Map(),
    db: null,
    selectedPaymentMethod: 'cash',
    selectedCustomerId: null,
};

const elements = {
    productList: document.getElementById('product-list'),
    cartList: document.getElementById('cart-list'),
    search: document.getElementById('product-search'),
    subtotal: document.getElementById('subtotal-value'),
    vat: document.getElementById('vat-value'),
    excise: document.getElementById('excise-value'),
    total: document.getElementById('total-value'),
    queueStatus: document.getElementById('queue-status'),
    syncButton: document.getElementById('sync-sales'),
    completeButton: document.getElementById('complete-sale'),
    paymentMethod: document.getElementById('payment-method'),
    customerSelect: document.getElementById('customer-select'),
};

function readBootstrap() {
    const script = document.getElementById('pos-bootstrap');

    if (!script) {
        return {
            sale_contract_version: 'unknown',
            shop: null,
            cashier: null,
            tax: { vat_enabled: true, vat_rate: 15 },
            products: [],
            shop_stock: {},
            customers: [],
        };
    }

    return JSON.parse(script.textContent || '{}');
}

function money(value) {
    return new Intl.NumberFormat(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
}

function quantity(value) {
    return Number.parseFloat(Number(value || 0).toFixed(3));
}

function toNumber(value) {
    return Number.parseFloat(Number(value || 0).toFixed(3));
}

function taxSettings() {
    return state.bootstrap.tax || { vat_enabled: true, vat_rate: 15 };
}

function lineTaxes(quantityValue, unitPrice, product) {
    const lineSubtotal = Number(quantityValue) * Number(unitPrice);
    const vatRate = taxSettings().vat_enabled ? Number(taxSettings().vat_rate || 0) : 0;
    const exciseRate = product?.is_excise_applicable ? Number(product.excise_rate || 0) : 0;

    return {
        lineSubtotal,
        vatRate,
        vatAmount: lineSubtotal * (vatRate / 100),
        exciseRate,
        exciseAmount: lineSubtotal * (exciseRate / 100),
    };
}

function productUnits(product) {
    return product?.units?.length ? product.units : [{ id: product?.base_unit_id, code: 'PCS', name: 'Piece', factor: 1 }];
}

function selectedUnit(product, unitId = null) {
    const units = productUnits(product);
    return units.find((unit) => Number(unit.id) === Number(unitId)) || units[0];
}

function unitFactor(product, unitId = null) {
    return Number(selectedUnit(product, unitId).factor || 1);
}

function effectiveBasePrice(product) {
    const customer = (state.bootstrap.customers || []).find((entry) => Number(entry.id) === Number(state.selectedCustomerId));
    const customerCategoryId = customer?.default_price_category_id;
    const shopCategoryId = state.bootstrap.shop?.default_price_category_id;

    if (customerCategoryId && product.prices?.[customerCategoryId] !== undefined) {
        return Number(product.prices[customerCategoryId]);
    }

    if (shopCategoryId && product.prices?.[shopCategoryId] !== undefined) {
        return Number(product.prices[shopCategoryId]);
    }

    return Number(product.price);
}

function effectiveUnitPrice(product, unitId = null) {
    return effectiveBasePrice(product) * unitFactor(product, unitId);
}

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(dbName, dbVersion);

        request.onupgradeneeded = () => {
            const database = request.result;

            if (!database.objectStoreNames.contains('products')) {
                database.createObjectStore('products', { keyPath: 'id' });
            }

            if (!database.objectStoreNames.contains('queue')) {
                database.createObjectStore('queue', { keyPath: 'idempotency_key' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function txRead(storeName, mode = 'readonly') {
    const transaction = state.db.transaction(storeName, mode);
    return transaction.objectStore(storeName);
}

function idbRequest(request) {
    return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function readAll(storeName) {
    return idbRequest(txRead(storeName).getAll());
}

async function putMany(storeName, items) {
    const transaction = state.db.transaction(storeName, 'readwrite');

    for (const item of items) {
        transaction.objectStore(storeName).put(item);
    }

    return new Promise((resolve, reject) => {
        transaction.oncomplete = () => resolve();
        transaction.onerror = () => reject(transaction.error);
        transaction.onabort = () => reject(transaction.error);
    });
}

async function putOne(storeName, item) {
    return idbRequest(state.db.transaction(storeName, 'readwrite').objectStore(storeName).put(item));
}

async function deleteOne(storeName, key) {
    return idbRequest(state.db.transaction(storeName, 'readwrite').objectStore(storeName).delete(key));
}

function buildProductIndex(products) {
    return new Map(products.map((product) => [Number(product.id), product]));
}

function currentCartQuantity(productId) {
    return Number(state.cart.get(Number(productId))?.base_quantity || 0);
}

function availableStock(product) {
    return Math.max(0, quantity(product.local_stock) - currentCartQuantity(product.id));
}

function filteredProducts() {
    const search = (elements.search?.value || '').trim().toLowerCase();

    if (!search) {
        return state.products;
    }

    return state.products.filter((product) => {
        return [product.name, product.sku, product.barcode]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(search));
    });
}

function renderProducts() {
    if (!elements.productList) {
        return;
    }

    const products = filteredProducts();

    if (products.length === 0) {
        elements.productList.innerHTML = '<div class="p-6 text-sm text-slate-400">No products match this search.</div>';
        return;
    }

    elements.productList.innerHTML = products.map((product) => {
        const stockLeft = availableStock(product);
        const disabled = stockLeft <= 0 ? 'disabled' : '';

        return `
            <article class="grid gap-3 bg-slate-950/70 px-4 py-4 sm:grid-cols-[1fr_auto] sm:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-semibold text-white">${escapeHtml(product.name)}</h3>
                        <span class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] uppercase tracking-[0.24em] text-slate-400">${escapeHtml(product.sku || 'No SKU')}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-400">Barcode ${escapeHtml(product.barcode || 'n/a')} · VAT ${escapeHtml(product.vat_rate)}%${product.is_excise_applicable ? ` · Excise ${escapeHtml(product.excise_rate)}%` : ''}</p>
                    <p class="mt-1 text-xs text-slate-500">Local stock ${stockLeft} ${escapeHtml(selectedUnit(product, product.base_unit_id).code || 'PCS')}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-lg font-semibold text-amber-300">${money(effectiveBasePrice(product))}</p>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Per ${escapeHtml(selectedUnit(product, product.base_unit_id).code || 'unit')}</p>
                    </div>
                    <button type="button" data-add-product="${product.id}" ${disabled} class="rounded-2xl bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition enabled:hover:bg-cyan-300 disabled:cursor-not-allowed disabled:bg-slate-700 disabled:text-slate-500">Add</button>
                </div>
            </article>
        `;
    }).join('');
}

function renderCart() {
    if (!elements.cartList) {
        return;
    }

    const cartEntries = Array.from(state.cart.entries());

    if (cartEntries.length === 0) {
        elements.cartList.innerHTML = '<div class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-sm text-slate-400">Cart is empty. Add products from the catalog.</div>';
        renderTotals(0, 0, 0, 0);
        return;
    }

    const index = buildProductIndex(state.products);

    let subtotal = 0;
    let vatTotal = 0;
    let exciseTotal = 0;

    elements.cartList.innerHTML = cartEntries.map(([productId, cartItem]) => {
        const product = index.get(Number(productId));
        const taxes = lineTaxes(cartItem.quantity, cartItem.unit_price, product || cartItem);
        const lineTotal = taxes.lineSubtotal + taxes.vatAmount + taxes.exciseAmount;
        const unitOptions = productUnits(product || cartItem).map((unit) => {
            const selected = Number(unit.id) === Number(cartItem.unit_id) ? 'selected' : '';

            return `<option value="${escapeHtml(unit.id)}" ${selected}>${escapeHtml(unit.code || unit.name)}</option>`;
        }).join('');

        subtotal += taxes.lineSubtotal;
        vatTotal += taxes.vatAmount;
        exciseTotal += taxes.exciseAmount;

        return `
            <article class="rounded-3xl border border-white/10 bg-white/5 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-white">${escapeHtml(product?.name || cartItem.product_name)}</h3>
                        <p class="mt-1 text-sm text-slate-400">${cartItem.quantity.toFixed(3)} ${escapeHtml(selectedUnit(product || cartItem, cartItem.unit_id).code || '')} × ${money(cartItem.unit_price)} · VAT ${taxes.vatRate}%${taxes.exciseRate > 0 ? ` · Excise ${taxes.exciseRate}%` : ''}</p>
                    </div>
                    <button type="button" data-remove-product="${productId}" class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-slate-300 transition hover:bg-white/10">Remove</button>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <input data-cart-quantity="${productId}" type="number" min="0.001" step="0.001" value="${cartItem.quantity.toFixed(3)}" class="rounded-2xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-slate-100 outline-none" />
                    <select data-cart-unit="${productId}" class="rounded-2xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-slate-100 outline-none">${unitOptions}</select>
                </div>
                <div class="mt-3 flex items-center justify-between text-sm text-slate-300">
                    <span>Line total</span>
                    <span>${money(lineTotal)}</span>
                </div>
            </article>
        `;
    }).join('');

    renderTotals(subtotal, vatTotal, exciseTotal, subtotal + vatTotal + exciseTotal);
}

function renderTotals(subtotal, vatTotal, exciseTotal, total) {
    if (elements.subtotal) elements.subtotal.textContent = money(subtotal);
    if (elements.vat) elements.vat.textContent = money(vatTotal);
    if (elements.excise) elements.excise.textContent = money(exciseTotal);
    if (elements.total) elements.total.textContent = money(total);
}

function renderQueueStatus() {
    const queued = state.queue.filter((sale) => sale.status === 'queued').length;
    const failed = state.queue.filter((sale) => sale.status === 'failed').length;
    const onlineLabel = navigator.onLine ? 'Online' : 'Offline';

    if (elements.queueStatus) {
        elements.queueStatus.textContent = `${onlineLabel} · ${queued} queued${failed > 0 ? ` · ${failed} failed` : ''}`;
    }
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function setCartQuantity(productId, quantityValue, unitId = null) {
    const product = buildProductIndex(state.products).get(Number(productId));

    if (!product) {
        return;
    }

    const safeQuantity = Math.max(0, quantityValue);
    const selectedUnitId = unitId || state.cart.get(Number(productId))?.unit_id || product.base_unit_id;
    const baseQuantity = quantity(safeQuantity * unitFactor(product, selectedUnitId));
    const existingQuantity = currentCartQuantity(productId);
    const stockLimit = quantity(product.local_stock);

    if (baseQuantity > stockLimit && baseQuantity > existingQuantity) {
        window.alert('Local stock is not enough for that quantity.');
        return;
    }

    if (safeQuantity <= 0) {
        state.cart.delete(Number(productId));
    } else {
        state.cart.set(Number(productId), {
            product_id: product.id,
            product_name: product.name,
            sku: product.sku,
            barcode: product.barcode,
            unit_id: Number(selectedUnitId),
            unit_code: selectedUnit(product, selectedUnitId).code,
            units: productUnits(product),
            quantity: quantity(safeQuantity),
            base_quantity: baseQuantity,
            unit_price: Number(effectiveUnitPrice(product, selectedUnitId).toFixed(2)),
            vat_rate: Number(product.vat_rate),
            is_excise_applicable: Boolean(product.is_excise_applicable),
            excise_rate: Number(product.excise_rate || 0),
        });
    }

    renderProducts();
    renderCart();
}

function addToCart(productId) {
    const product = buildProductIndex(state.products).get(Number(productId));

    if (!product) {
        return;
    }

    const existing = state.cart.get(Number(productId));
    const selectedUnitId = existing?.unit_id || product.base_unit_id;
    const nextQuantity = Number(existing?.quantity || 0) + 1;
    const nextBaseQuantity = quantity(nextQuantity * unitFactor(product, selectedUnitId));

    if (nextBaseQuantity > quantity(product.local_stock)) {
        window.alert('Local stock is not enough for that quantity.');
        return;
    }

    setCartQuantity(productId, nextQuantity, selectedUnitId);
}

function removeFromCart(productId) {
    const existing = state.cart.get(Number(productId));
    setCartQuantity(productId, Number(existing?.quantity || 0) - 1, existing?.unit_id);
}

async function seedProducts() {
    const products = state.bootstrap.products.map((product) => ({
        ...product,
        price: Number(product.price),
        prices: product.prices || {},
        units: (product.units || []).map((unit) => ({ ...unit, factor: Number(unit.factor || 1) })),
        vat_rate: Number(product.vat_rate),
        is_excise_applicable: Boolean(product.is_excise_applicable),
        excise_rate: Number(product.excise_rate || 0),
        local_stock: Number(product.local_stock || 0),
    }));

    await putMany('products', products);
}

async function loadProducts() {
    const products = await readAll('products');

    if (products.length === 0) {
        await seedProducts();
        return readAll('products');
    }

    return products;
}

async function loadQueue() {
    return readAll('queue');
}

function buildSalePayload(cartEntries) {
    const soldAt = new Date().toISOString();
    const idempotencyKey = crypto.randomUUID();
    const paymentMethod = state.selectedPaymentMethod || 'cash';
    const customerId = paymentMethod === 'credit_account' ? state.selectedCustomerId : null;

    if (paymentMethod === 'credit_account' && !customerId) {
        throw new Error('Select a customer for credit-account sales.');
    }

    const items = cartEntries.map(([productId, item]) => {
        const taxes = lineTaxes(item.quantity, item.unit_price, item);

        return {
            product_id: Number(productId),
            product_name: item.product_name,
            sku: item.sku || null,
            barcode: item.barcode || null,
            unit_id: item.unit_id || null,
            quantity: Number(item.quantity).toFixed(3),
            unit_price: Number(item.unit_price).toFixed(2),
            vat_rate: taxes.vatRate.toFixed(2),
            vat_amount: taxes.vatAmount.toFixed(2),
            line_total: (taxes.lineSubtotal + taxes.vatAmount + taxes.exciseAmount).toFixed(2),
        };
    });

    const subtotal = items.reduce((sum, item) => sum + Number(item.quantity) * Number(item.unit_price), 0);
    const vatTotal = items.reduce((sum, item) => sum + Number(item.vat_amount), 0);
    const exciseTotal = cartEntries.reduce((sum, [, item]) => sum + lineTaxes(item.quantity, item.unit_price, item).exciseAmount, 0);

    return {
        idempotency_key: idempotencyKey,
        shop_id: Number(state.bootstrap.shop.id),
        cashier_id: Number(state.bootstrap.cashier.id),
        sold_at: soldAt,
        subtotal: subtotal.toFixed(2),
        vat_total: vatTotal.toFixed(2),
        total: (subtotal + vatTotal + exciseTotal).toFixed(2),
        payment_method: paymentMethod,
        customer_id: customerId,
        items,
    };
}

function renderCustomers() {
    if (!elements.customerSelect) {
        return;
    }

    const customers = state.bootstrap.customers || [];

    const options = ['<option value="">Select customer</option>']
        .concat(customers.map((customer) => {
            return `<option value="${customer.id}">${escapeHtml(customer.name)} (${escapeHtml(customer.code)})</option>`;
        }));

    elements.customerSelect.innerHTML = options.join('');
}

function syncPaymentUiState() {
    const isCredit = state.selectedPaymentMethod === 'credit_account';

    if (elements.customerSelect) {
        elements.customerSelect.disabled = !isCredit;
    }

    if (!isCredit) {
        state.selectedCustomerId = null;
        if (elements.customerSelect) {
            elements.customerSelect.value = '';
        }
    }
}

async function queueCurrentSale() {
    if (state.cart.size === 0) {
        window.alert('Add at least one item before queuing a sale.');
        return;
    }

    const sale = buildSalePayload(Array.from(state.cart.entries()));

    await putOne('queue', {
        ...sale,
        status: 'queued',
        last_error: null,
        queued_at: new Date().toISOString(),
    });

    for (const [productId, cartItem] of state.cart.entries()) {
        const product = state.products.find((entry) => Number(entry.id) === Number(productId));

        if (!product) {
            continue;
        }

        product.local_stock = quantity(Number(product.local_stock) - Number(cartItem.base_quantity));
        await putOne('products', product);
    }

    state.cart.clear();
    state.products = await loadProducts();
    state.queue = await loadQueue();
    renderProducts();
    renderCart();
    renderQueueStatus();
}

async function syncQueuedSales() {
    const queuedSales = state.queue.filter((sale) => sale.status === 'queued');

    if (queuedSales.length === 0) {
        window.alert('No queued sales to sync.');
        return;
    }

    if (!navigator.onLine) {
        window.alert('You are offline. Sync will resume when the browser reconnects.');
        return;
    }

    const response = await fetch('/api/pos/sync', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            sales: queuedSales.map((sale) => ({
                idempotency_key: sale.idempotency_key,
                shop_id: sale.shop_id,
                cashier_id: sale.cashier_id,
                sold_at: sale.sold_at,
                subtotal: sale.subtotal,
                vat_total: sale.vat_total,
                total: sale.total,
                payment_method: sale.payment_method || 'cash',
                customer_id: sale.customer_id || null,
                items: sale.items,
            })),
        }),
    });

    const payload = await response.json();
    const results = payload.results || [];

    for (const result of results) {
        if (result.status === 'synced' || result.status === 'duplicate') {
            await deleteOne('queue', result.idempotency_key);
            continue;
        }

        const sale = state.queue.find((entry) => entry.idempotency_key === result.idempotency_key);

        if (sale) {
            await putOne('queue', {
                ...sale,
                status: 'failed',
                last_error: result.message || 'Sync rejected by server.',
            });
        }
    }

    state.queue = await loadQueue();
    renderQueueStatus();
    window.alert('Sync complete. Review any failed sales in the queue.');
}

function wireEvents() {
    elements.search?.addEventListener('input', () => {
        renderProducts();
    });

    elements.paymentMethod?.addEventListener('change', () => {
        state.selectedPaymentMethod = elements.paymentMethod.value || 'cash';
        syncPaymentUiState();
    });

    elements.customerSelect?.addEventListener('change', () => {
        const value = elements.customerSelect.value;
        state.selectedCustomerId = value ? Number(value) : null;

        for (const [productId, cartItem] of state.cart.entries()) {
            setCartQuantity(productId, cartItem.quantity, cartItem.unit_id);
        }

        renderProducts();
        renderCart();
    });

    elements.productList?.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-add-product]');

        if (addButton) {
            addToCart(addButton.dataset.addProduct);
        }
    });

    elements.cartList?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-product]');

        if (removeButton) {
            removeFromCart(removeButton.dataset.removeProduct);
        }
    });

    elements.cartList?.addEventListener('change', (event) => {
        const quantityInput = event.target.closest('[data-cart-quantity]');
        const unitSelect = event.target.closest('[data-cart-unit]');

        if (quantityInput) {
            const productId = quantityInput.dataset.cartQuantity;
            const existing = state.cart.get(Number(productId));
            setCartQuantity(productId, Number(quantityInput.value || 0), existing?.unit_id);
        }

        if (unitSelect) {
            const productId = unitSelect.dataset.cartUnit;
            const existing = state.cart.get(Number(productId));
            setCartQuantity(productId, existing?.quantity || 1, Number(unitSelect.value));
        }
    });

    elements.completeButton?.addEventListener('click', () => {
        queueCurrentSale().catch((error) => {
            console.error(error);
            window.alert('Could not queue the sale.');
        });
    });

    elements.syncButton?.addEventListener('click', () => {
        syncQueuedSales().catch((error) => {
            console.error(error);
            window.alert('Could not sync queued sales.');
        });
    });

    window.addEventListener('online', renderQueueStatus);
    window.addEventListener('offline', renderQueueStatus);
}

async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    try {
        await navigator.serviceWorker.register('/sw.js');
    } catch (error) {
        console.error('Service worker registration failed', error);
    }
}

async function bootstrap() {
    state.db = await openDatabase();
    state.products = await loadProducts();
    state.queue = await loadQueue();

    renderCustomers();
    syncPaymentUiState();

    wireEvents();
    renderProducts();
    renderCart();
    renderQueueStatus();
    await registerServiceWorker();
}

bootstrap().catch((error) => {
    console.error(error);
    window.alert('The POS shell failed to start. Refresh the page and try again.');
});
