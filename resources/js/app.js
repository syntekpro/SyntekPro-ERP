import './bootstrap';

const preferenceUrl = document.querySelector('meta[name="user-interface-preferences-url"]')?.content;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const systemThemeMedia = window.matchMedia('(prefers-color-scheme: dark)');
const desktopDrawerMedia = window.matchMedia('(min-width: 64rem)');
const drawerStorageKey = 'shell:drawer-collapsed';
const quickMenuHiddenItemsStorageKey = 'shell:quick-menu-hidden-items';
const headerNotificationsDismissedStorageKey = 'shell:header-notifications-dismissed';

const resolveTheme = (preference) => {
	if (preference === 'dark' || preference === 'light') {
		return preference;
	}

	return systemThemeMedia.matches ? 'dark' : 'light';
};

const prettyThemeLabel = (preference) => {
	if (preference === 'light') {
		return 'Light';
	}

	if (preference === 'dark') {
		return 'Dark';
	}

	return 'Auto';
};

const persistPreferences = (payload) => {
	if (!preferenceUrl || !csrfToken) {
		return;
	}

	fetch(preferenceUrl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-CSRF-TOKEN': csrfToken,
			'Accept': 'application/json',
		},
		body: JSON.stringify(payload),
	}).catch(() => {});
};

const setTheme = (preference) => {
	const root = document.documentElement;
	root.dataset.themePreference = preference;
	root.dataset.theme = resolveTheme(preference);
	document.querySelectorAll('[data-theme-toggle-label]').forEach((label) => {
		label.textContent = prettyThemeLabel(preference);
	});
};

const cycleThemePreference = (preference) => {
	if (preference === 'light') {
		return 'dark';
	}

	if (preference === 'dark') {
		return 'system';
	}

	return 'light';
};

const currentThemePreference = () => document.documentElement.dataset.themePreference || 'system';

const updateHeaderClock = () => {
	const dateTarget = document.querySelector('[data-header-date]');
	const timeTarget = document.querySelector('[data-header-time]');

	if (!dateTarget && !timeTarget) {
		return;
	}

	const now = new Date();
	if (dateTarget) {
		dateTarget.textContent = new Intl.DateTimeFormat(undefined, {
			weekday: 'short',
			day: '2-digit',
			month: 'short',
			year: 'numeric',
		}).format(now);
	}

	if (timeTarget) {
		timeTarget.textContent = new Intl.DateTimeFormat(undefined, {
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
			hour12: false,
		}).format(now);
	}
};

setTheme(currentThemePreference());
updateHeaderClock();
window.setInterval(updateHeaderClock, 1000);

if (document.body?.dataset.persistThemeDefault === 'true') {
	setTheme('system');
	persistPreferences({ theme_mode: 'system' });
}

document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
	toggle.addEventListener('click', () => {
		const nextThemePreference = cycleThemePreference(currentThemePreference());
		setTheme(nextThemePreference);
		persistPreferences({ theme_mode: nextThemePreference });
	});
});

systemThemeMedia.addEventListener('change', () => {
	if (currentThemePreference() === 'system') {
		setTheme('system');
	}
});

const applyDrawerState = () => {
	if (!desktopDrawerMedia.matches) {
		document.body.classList.remove('shell-drawer-collapsed');
		return;
	}

	const collapsed = window.localStorage.getItem(drawerStorageKey) === '1';
	document.body.classList.toggle('shell-drawer-collapsed', collapsed);
	document.body.classList.remove('shell-drawer-open');
};

const openDrawer = () => {
	if (!desktopDrawerMedia.matches) {
		document.body.classList.add('shell-drawer-open');
		document.querySelector('[data-shell-overlay]')?.classList.remove('hidden');
	}
};

const closeDrawer = () => {
	document.body.classList.remove('shell-drawer-open');
	document.querySelector('[data-shell-overlay]')?.classList.add('hidden');
};

const toggleDrawer = () => {
	if (desktopDrawerMedia.matches) {
		const collapsed = !document.body.classList.contains('shell-drawer-collapsed');
		document.body.classList.toggle('shell-drawer-collapsed', collapsed);
		window.localStorage.setItem(drawerStorageKey, collapsed ? '1' : '0');
		return;
	}

	if (document.body.classList.contains('shell-drawer-open')) {
		closeDrawer();
		return;
	}

	openDrawer();
};

const setSurfaceVisibility = (id, visible, type) => {
	const target = document.getElementById(id);
	if (!target) {
		return;
	}

	target.classList.toggle('hidden', !visible);
	target.setAttribute('aria-hidden', visible ? 'false' : 'true');
	target.dataset.uiState = visible ? 'open' : 'closed';

	if (!visible && type === 'drawer') {
		target.querySelector('[data-ui-drawer-autofocus]')?.blur();
	}
};

const openSurface = (id, type) => {
	setSurfaceVisibility(id, true, type);
	document.body.classList.add('overflow-hidden');
};

const closeSurface = (id, type) => {
	setSurfaceVisibility(id, false, type);

	const openSurfaces = document.querySelectorAll('[data-ui-modal]:not(.hidden), [data-ui-drawer]:not(.hidden)');
	if (openSurfaces.length === 0) {
		document.body.classList.remove('overflow-hidden');
	}
};

document.querySelectorAll('[data-ui-modal-open]').forEach((trigger) => {
	trigger.addEventListener('click', () => {
		const targetId = trigger.getAttribute('data-ui-modal-open');
		if (targetId) {
			openSurface(targetId, 'modal');
		}
	});
});

document.querySelectorAll('[data-ui-modal-close]').forEach((trigger) => {
	trigger.addEventListener('click', () => {
		const targetId = trigger.getAttribute('data-ui-modal-close');
		if (targetId) {
			closeSurface(targetId, 'modal');
		}
	});
});

document.querySelectorAll('[data-ui-drawer-open]').forEach((trigger) => {
	trigger.addEventListener('click', () => {
		const targetId = trigger.getAttribute('data-ui-drawer-open');
		if (targetId) {
			openSurface(targetId, 'drawer');
		}
	});
});

document.querySelectorAll('[data-ui-drawer-close]').forEach((trigger) => {
	trigger.addEventListener('click', () => {
		const targetId = trigger.getAttribute('data-ui-drawer-close');
		if (targetId) {
			closeSurface(targetId, 'drawer');
		}
	});
});

document.querySelectorAll('[data-shell-drawer-toggle]').forEach((button) => {
	button.addEventListener('click', toggleDrawer);
});

document.querySelectorAll('[data-shell-drawer-close]').forEach((button) => {
	button.addEventListener('click', closeDrawer);
});

document.querySelector('[data-shell-overlay]')?.addEventListener('click', closeDrawer);

// Quick Menu: hover-intent open/close on mouse-capable devices, while leaving
// native click-to-toggle (touch) and keyboard (focus + Enter/Space via the
// native <details>/<summary> element) behavior completely untouched.
const hoverCapableMedia = window.matchMedia('(hover: hover) and (pointer: fine)');
const QUICK_MENU_OPEN_DELAY = 150;
const QUICK_MENU_CLOSE_DELAY = 150;

document.querySelectorAll('[data-quick-menu]').forEach((menu) => {
	let openTimer = null;
	let closeTimer = null;
	const customizeToggle = menu.querySelector('[data-quick-menu-customize-toggle]');
	const customizePanel = menu.querySelector('[data-quick-menu-customize-panel]');
	const customizeClose = menu.querySelector('[data-quick-menu-customize-close]');
	const customizeReset = menu.querySelector('[data-quick-menu-reset]');
	const visibilityToggles = [...menu.querySelectorAll('[data-quick-menu-item-toggle]')];
	const menuItems = [...menu.querySelectorAll('[data-quick-menu-item][data-quick-menu-item-key]')];
	const menuSections = [...menu.querySelectorAll('[data-quick-menu-section][data-quick-menu-section-key]')];
	const persistedHiddenItems = (window.localStorage.getItem(quickMenuHiddenItemsStorageKey) || '')
		.split(',')
		.map((itemKey) => itemKey.trim())
		.filter((itemKey) => itemKey.length > 0);
	let hiddenItemKeys = new Set(
		persistedHiddenItems.length > 0
			? persistedHiddenItems
			: visibilityToggles.filter((toggle) => !toggle.checked).map((toggle) => toggle.value),
	);

	const clearTimers = () => {
		if (openTimer) {
			window.clearTimeout(openTimer);
			openTimer = null;
		}

		if (closeTimer) {
			window.clearTimeout(closeTimer);
			closeTimer = null;
		}
	};

	const persistQuickMenuState = () => {
		const hiddenItemsList = [...hiddenItemKeys];
		window.localStorage.setItem(quickMenuHiddenItemsStorageKey, hiddenItemsList.join(','));
		persistPreferences({
			navigation_state: {
				quick_menu_hidden_items: hiddenItemsList,
			},
		});
	};

	const syncCustomizeToggles = () => {
		visibilityToggles.forEach((toggle) => {
			toggle.checked = !hiddenItemKeys.has(toggle.value);
		});
	};

	const applyQuickMenuVisibility = () => {
		menuItems.forEach((menuItem) => {
			const itemKey = menuItem.getAttribute('data-quick-menu-item-key');
			if (!itemKey) {
				return;
			}

			menuItem.classList.toggle('hidden', hiddenItemKeys.has(itemKey));
		});

		menuSections.forEach((menuSection) => {
			const sectionKey = menuSection.getAttribute('data-quick-menu-section-key');
			if (!sectionKey) {
				return;
			}

			const visibleItemsInSection = menuItems.filter((menuItem) => {
				if (menuItem.classList.contains('hidden')) {
					return false;
				}

				return menuItem.getAttribute('data-quick-menu-section-key') === sectionKey;
			});

			menuSection.classList.toggle('quick-menu-column-empty', visibleItemsInSection.length === 0);
		});
	};

	const closeCustomizePanel = () => {
		if (!customizePanel || !customizeToggle) {
			return;
		}

		customizePanel.classList.add('hidden');
		customizeToggle.setAttribute('aria-expanded', 'false');
	};

	const openCustomizePanel = () => {
		if (!customizePanel || !customizeToggle) {
			return;
		}

		customizePanel.classList.remove('hidden');
		customizeToggle.setAttribute('aria-expanded', 'true');
	};

	const isCustomizePanelOpen = () => customizePanel ? !customizePanel.classList.contains('hidden') : false;

	syncCustomizeToggles();
	applyQuickMenuVisibility();

	visibilityToggles.forEach((toggle) => {
		toggle.addEventListener('change', () => {
			if (toggle.checked) {
				hiddenItemKeys.delete(toggle.value);
			} else {
				hiddenItemKeys.add(toggle.value);
			}

			applyQuickMenuVisibility();
			persistQuickMenuState();
		});
	});

	customizeReset?.addEventListener('click', () => {
		hiddenItemKeys = new Set();
		syncCustomizeToggles();
		applyQuickMenuVisibility();
		persistQuickMenuState();
	});

	customizeClose?.addEventListener('click', closeCustomizePanel);

	customizeToggle?.addEventListener('click', (event) => {
		event.preventDefault();
		event.stopPropagation();

		if (isCustomizePanelOpen()) {
			closeCustomizePanel();
			return;
		}

		menu.open = true;
		openCustomizePanel();
	});

	document.addEventListener('click', (event) => {
		if (!isCustomizePanelOpen()) {
			return;
		}

		if (!(event.target instanceof Node)) {
			return;
		}

		if (!menu.contains(event.target)) {
			closeCustomizePanel();
		}
	});

	menu.addEventListener('toggle', () => {
		if (!menu.open) {
			closeCustomizePanel();
		}
	});

	menu.addEventListener('mouseenter', () => {
		if (!hoverCapableMedia.matches) {
			return;
		}

		clearTimers();
		openTimer = window.setTimeout(() => {
			menu.open = true;
		}, QUICK_MENU_OPEN_DELAY);
	});

	menu.addEventListener('mouseleave', () => {
		if (!hoverCapableMedia.matches) {
			return;
		}

		clearTimers();
		closeTimer = window.setTimeout(() => {
			if (isCustomizePanelOpen()) {
				return;
			}

			menu.open = false;
		}, QUICK_MENU_CLOSE_DELAY);
	});
});

const initializeHeaderNotifications = () => {
	document.querySelectorAll('[data-header-notifications]').forEach((notificationsMenu) => {
		if (notificationsMenu.dataset.notificationsInitialized === 'true') {
			return;
		}

		notificationsMenu.dataset.notificationsInitialized = 'true';
		const badge = notificationsMenu.querySelector('[data-header-notification-badge]');
		const emptyState = notificationsMenu.querySelector('[data-header-notification-empty]');
		const markAllButton = notificationsMenu.querySelector('[data-header-notification-mark-all]');
		const dismissButtons = [...notificationsMenu.querySelectorAll('[data-header-notification-dismiss]')];
		const initialDismissed = (notificationsMenu.getAttribute('data-header-notification-dismissed') || '')
			.split(',')
			.map((notificationId) => notificationId.trim())
			.filter((notificationId) => notificationId.length > 0);
		const persistedDismissed = (window.localStorage.getItem(headerNotificationsDismissedStorageKey) || '')
			.split(',')
			.map((notificationId) => notificationId.trim())
			.filter((notificationId) => notificationId.length > 0);
		const dismissedNotificationIds = new Set(persistedDismissed.length > 0 ? persistedDismissed : initialDismissed);
		const notificationItems = () => [...notificationsMenu.querySelectorAll('[data-header-notification-item]')];

		const persistNotificationsState = () => {
			const dismissedNotifications = [...dismissedNotificationIds];
			window.localStorage.setItem(headerNotificationsDismissedStorageKey, dismissedNotifications.join(','));
			persistPreferences({
				navigation_state: {
					header_notifications_dismissed: dismissedNotifications,
				},
			});
		};

		const syncNotifications = () => {
			let unreadCount = 0;

			notificationItems().forEach((item) => {
				const notificationId = item.getAttribute('data-header-notification-id');
				const isDismissed = notificationId ? dismissedNotificationIds.has(notificationId) : false;
				item.classList.toggle('hidden', isDismissed);
				if (!isDismissed) {
					unreadCount += 1;
				}
			});

			badge?.classList.toggle('hidden', unreadCount === 0);
			if (badge && unreadCount > 0) {
				badge.textContent = `${unreadCount}`;
			}
			emptyState?.classList.toggle('hidden', unreadCount > 0);
			if (markAllButton) {
				markAllButton.disabled = unreadCount === 0;
			}
		};

		dismissButtons.forEach((dismissButton) => {
			dismissButton.addEventListener('click', (event) => {
				event.preventDefault();
				event.stopPropagation();
				const notificationId = dismissButton.closest('[data-header-notification-item]')?.getAttribute('data-header-notification-id');
				if (!notificationId) {
					return;
				}

				dismissedNotificationIds.add(notificationId);
				syncNotifications();
				persistNotificationsState();
			});
		});

		markAllButton?.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			notificationItems().forEach((item) => {
				const notificationId = item.getAttribute('data-header-notification-id');
				if (notificationId) {
					dismissedNotificationIds.add(notificationId);
				}
			});
			syncNotifications();
			persistNotificationsState();
		});

		syncNotifications();
	});
};

initializeHeaderNotifications();

desktopDrawerMedia.addEventListener('change', () => {
	applyDrawerState();
	closeDrawer();
});

applyDrawerState();

const persistCollapsedSections = () => {
	const collapsedSections = [...document.querySelectorAll('[data-nav-section]')]
		.filter((section) => section.querySelector('[data-nav-section-panel]')?.classList.contains('hidden'))
		.map((section) => section.dataset.navSection);

	persistPreferences({ navigation_state: { collapsed_sections: collapsedSections } });
};

document.querySelectorAll('[data-nav-section-toggle]').forEach((toggle) => {
	toggle.addEventListener('click', () => {
		if (desktopDrawerMedia.matches && document.body.classList.contains('shell-drawer-collapsed')) {
			toggleDrawer();
			return;
		}

		const section = toggle.closest('[data-nav-section]');
		const panel = section?.querySelector('[data-nav-section-panel]');
		const chevron = section?.querySelector('[data-nav-chevron]');

		if (!section || !panel) {
			return;
		}

		const willCollapse = !panel.classList.contains('hidden');
		panel.classList.toggle('hidden', willCollapse);
		toggle.setAttribute('aria-expanded', willCollapse ? 'false' : 'true');
		chevron?.classList.toggle('-rotate-90', willCollapse);
		persistCollapsedSections();
	});
});

const palette = document.querySelector('[data-command-palette]');
const commandInput = document.querySelector('[data-command-input]');
const commandResults = document.querySelector('[data-command-results]');
const commandSource = document.getElementById('navigation-commands');
const commands = commandSource ? JSON.parse(commandSource.textContent || '[]') : [];

const renderCommands = (query = '') => {
	if (!commandResults) {
		return;
	}

	const normalizedQuery = query.trim().toLowerCase();
	const visibleCommands = commands
		.filter((command) => `${command.label} ${command.section}`.toLowerCase().includes(normalizedQuery))
		.slice(0, 12);

	commandResults.innerHTML = visibleCommands.map((command, index) => `
		<a href="${command.url}" class="flex items-center justify-between rounded-ui px-3 py-3 text-sm text-ink hover:bg-brass/10 ${index === 0 ? 'bg-brass/10' : ''}">
			<span>${command.label}</span>
			<span class="text-xs uppercase tracking-[0.2em] text-subtle">${command.section}</span>
		</a>
	`).join('') || '<p class="px-3 py-6 text-center text-sm text-muted">No matching screens.</p>';
};

const openPalette = () => {
	if (!palette || !commandInput) {
		return;
	}

	palette.classList.remove('hidden');
	palette.setAttribute('aria-hidden', 'false');
	renderCommands(commandInput.value);
	commandInput.focus();
};

const closePalette = () => {
	palette?.classList.add('hidden');
	palette?.setAttribute('aria-hidden', 'true');
};

document.querySelectorAll('[data-command-open]').forEach((button) => button.addEventListener('click', openPalette));
commandInput?.addEventListener('input', (event) => renderCommands(event.target.value));
palette?.addEventListener('click', (event) => {
	if (event.target === palette) {
		closePalette();
	}
});

document.addEventListener('keydown', (event) => {
	const target = event.target;
	const isTypingTarget = target instanceof HTMLElement
		&& (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

	if (!isTypingTarget && (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
		event.preventDefault();
		toggleDrawer();
	}

	if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
		event.preventDefault();
		openPalette();
	}

	if (event.key === 'Escape') {
		closeDrawer();
		closePalette();
		document.querySelectorAll('[data-quick-menu-customize-panel]').forEach((panel) => panel.classList.add('hidden'));
		document.querySelectorAll('[data-quick-menu-customize-toggle]').forEach((toggle) => toggle.setAttribute('aria-expanded', 'false'));

		document.querySelectorAll('[data-ui-modal]:not(.hidden)').forEach((modal) => {
			if (modal.id) {
				closeSurface(modal.id, 'modal');
			}
		});

		document.querySelectorAll('[data-ui-drawer]:not(.hidden)').forEach((drawer) => {
			if (drawer.id) {
				closeSurface(drawer.id, 'drawer');
			}
		});
	}
});

const applyDesignSystemBaseline = () => {
	document.querySelectorAll('table').forEach((table) => {
		table.classList.add('table-baseline');
	});

	document.querySelectorAll('td, th, span, p, li, div').forEach((element) => {
		if (element.children.length > 0) {
			return;
		}

		const text = element.textContent?.trim() || '';
			const hasFinancialNumber = /\bSAR\s+-?\d|^-?\d{1,3}(,\d{3})*(\.\d{2,3})?%?$|^-?\d+(\.\d{2,3})?$/.test(text);
			if (hasFinancialNumber) {
			element.classList.add('figure-mono');
		}

			if (hasFinancialNumber && /^(Total|Grand total|Gross Profit|Net Income|Net cash|Operating cash flow)\b/i.test(text)) {
			element.classList.add('ledger-total');
		}
	});

	document.querySelectorAll('td, span').forEach((element) => {
		const text = element.textContent?.trim().toLowerCase() || '';
		if (!text || element.classList.contains('status-pill')) {
			return;
		}

		if (['paid', 'received', 'closed', 'active', 'balanced'].includes(text)) {
			element.classList.add('status-pill', 'status-pill-success');
		}

		if (['pending', 'draft', 'submitted', 'partially received', 'in transit', 'open'].includes(text)) {
			element.classList.add('status-pill', 'status-pill-neutral');
		}

		if (['overdue', 'void', 'inactive', 'out of balance', 'cancelled'].includes(text)) {
			element.classList.add('status-pill', 'status-pill-danger');
		}
	});
};

applyDesignSystemBaseline();
document.addEventListener('livewire:navigated', applyDesignSystemBaseline);
document.addEventListener('livewire:update', applyDesignSystemBaseline);

if (document.body?.dataset.posShell === 'true') {
	import('./pos');
}
