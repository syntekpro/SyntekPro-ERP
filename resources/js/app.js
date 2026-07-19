import './bootstrap';

const preferenceUrl = document.querySelector('meta[name="user-interface-preferences-url"]')?.content;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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

const setTheme = (theme) => {
	const root = document.documentElement;
	root.dataset.theme = theme;
	root.dataset.themePreference = theme;
	document.querySelectorAll('[data-theme-toggle-label]').forEach((label) => {
		label.textContent = theme;
	});
};

if (document.body?.dataset.persistThemeDefault === 'true') {
	const preferred = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
	setTheme(preferred);
	persistPreferences({ theme_mode: preferred });
}

document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
	toggle.addEventListener('click', () => {
		const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
		setTheme(nextTheme);
		persistPreferences({ theme_mode: nextTheme });
	});
});

const persistCollapsedSections = () => {
	const collapsedSections = [...document.querySelectorAll('[data-nav-section]')]
		.filter((section) => section.querySelector('[data-nav-section-panel]')?.classList.contains('hidden'))
		.map((section) => section.dataset.navSection);

	persistPreferences({ navigation_state: { collapsed_sections: collapsedSections } });
};

document.querySelectorAll('[data-nav-section-toggle]').forEach((toggle) => {
	toggle.addEventListener('click', () => {
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
	if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
		event.preventDefault();
		openPalette();
	}

	if (event.key === 'Escape') {
		closePalette();
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
		if (/\bSAR\s+-?\d|^-?\d{1,3}(,\d{3})*(\.\d{2,3})?%?$/.test(text)) {
			element.classList.add('figure-mono');
		}

		if (/^(Total|Grand total|Gross Profit|Net Income|Net cash|Operating cash flow|Assets = Liabilities \+ Equity)/i.test(text)) {
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
