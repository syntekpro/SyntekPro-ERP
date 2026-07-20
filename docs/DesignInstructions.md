\# SyntekERP Design Instructions



Version: 1.0



This document defines the complete design language for SyntekERP.



These rules MUST be followed before creating or modifying any interface.



\---



\# Core Philosophy



SyntekERP is NOT another admin dashboard.



It is premium enterprise financial software.



The interface should feel:



\- Calm

\- Precise

\- Spacious

\- Professional

\- Fast

\- Modern

\- Timeless



Inspired by:



\- Linear

\- Stripe

\- IBM Carbon

\- Apple Human Interface

\- Arc Browser

\- Notion

\- Bloomberg Terminal (clarity)

\- Professional accounting software



Never imitate:



\- ERPNext

\- Odoo

\- Bootstrap Admin

\- AdminLTE

\- Metronic



\---



\# White Label First



SyntekERP must support complete white-label branding.



Never hardcode:



\- SyntekERP

\- SyntekPro

\- Logos

\- Favicons

\- Brand colors

\- Company names



Everything must come from the Branding Engine.



Example



Brand Settings



Application Name



BusinessFlow ERP



Logo



logo.svg



Primary Color



\#0B1F2A



Accent Color



\#C59B3A



Favicon



favicon.ico



Short Name



BFE



Footer



Powered by BusinessFlow



All UI components must automatically update when branding changes.



\---



\# Brand Configuration



Support:



Application Name



Application Short Name



Company Name



Logo (Light)



Logo (Dark)



Icon



Favicon



Login Background



Email Logo



PDF Logo



Receipt Logo



Invoice Logo



Primary Color



Secondary Color



Accent Color



Sidebar Color



Header Color



Chart Colors



Button Radius



Theme



Typography



Animations



Watermark



Support URL



Website



Copyright



Everything must be configurable.



\---



\# Color Palette



Default Theme



Ledger Navy



\#0B1F2A



Paper Grey



\#F5F6F8



White



\#FFFFFF



Brass



\#C59B3A



Ledger Green



\#4E8B68



Muted Rust



\#A35D45



Border



\#E4E7EC



Text



\#111827



Secondary Text



\#6B7280



These are defaults only.



Every color must be replaceable.



\---



\# Typography



Primary



IBM Plex Sans



Numbers



IBM Plex Mono



All financial figures



Invoices



Reports



Tables



Totals



must use IBM Plex Mono.



\---



\# Financial Identity



Final totals should receive a brass double underline.



Examples



Invoice Total



Balance



Revenue



Profit



Cash



Trial Balance



Never overuse.



This is the visual identity of SyntekERP.



\---



\# Layout



Desktop



Fixed Header



Retractable Navigation Drawer



Main Content



Footer



Navigation Drawer



Collapsed



Icons only



Expanded



Icons + Labels



Animated



Keyboard shortcut



CTRL+B



No permanent sidebar.



\---



\# Header



Contains



Brand Logo



Search



Command Palette



Quick Actions



Notifications



Tasks



Messages



Theme Toggle



Language



Workspace



Current Company



Current Branch



Current Warehouse



Profile



Date



Clock



\---



\# Dashboard



Dashboard should immediately communicate business health.



Cards



Revenue



Sales



Expenses



Profit



Customers



Suppliers



Inventory Value



Cash



Receivables



Payables



Charts



Revenue



Sales



Inventory



Cash Flow



Expenses



Top Products



Warehouse Stock



Recent Activity



Tasks



Calendar



Quick Actions



\---



\# Icons



Icons define the product identity.



Avoid generic flat icons.



Preferred



Premium textured icons



Glossy 3D icons



Soft metallic materials



Minimal reflections



Rounded geometry



Lucide icons are acceptable only as placeholders.



System should allow replacing icon packs globally.



\---



\# Theme Engine



Appearance Settings



Light



Dark



Auto



Accent Color



Navigation Style



Rounded Corners



Animations



Density



Typography



Chart Style



Card Radius



Glass Effects



Wallpaper



Pattern



Icon Pack



Every theme should use design tokens.



Never hardcode colors.



\---



\# Design Tokens



Every component must use variables.



Example



Primary



Background



Surface



Border



Success



Warning



Danger



Radius



Spacing



Shadow



Animation



Never use raw hex values inside components.



\---



\# Tables



Sticky Header



Resizable Columns



Filters



Saved Views



Bulk Actions



Column Visibility



Pagination



Search



Right align money.



Use monospace numbers.



Professional spacing.



\---



\# Forms



Large click targets.



Rounded inputs.



Floating labels.



Keyboard accessible.



Consistent spacing.



\---



\# Buttons



Primary



Secondary



Ghost



Danger



Success



Loading



Disabled



Never invent button styles.



Reuse shared component.



\---



\# Components



Everything reusable.



Card



Table



Chart



Modal



Drawer



Dropdown



Input



Toast



Alert



Badge



Stat Card



Search



Breadcrumb



Tabs



Wizard



Timeline



Never duplicate components.



\---



\# Motion



Subtle only.



Fade



Slide



Scale



Hover



Drawer



Chart



No flashy animations.



Animations must improve usability.



\---



\# Accessibility



Keyboard navigation.



Screen reader support.



RTL ready.



Arabic ready.



High contrast.



Visible focus.



\---



\# Responsive



Desktop first.



Tablet optimized.



Mobile navigation drawer.



Touch-friendly controls.



\---



\# Coding Rules



Never duplicate styles.



Never duplicate components.



Never hardcode colors.



Never hardcode logos.



Never hardcode names.



Everything configurable.



\---



\# Development Workflow



Before creating a page



1\. Check existing component.



2\. Reuse existing component.



3\. If unavailable



Create reusable component.



4\. Add to component library.



5\. Then build page.



Never build isolated pages.



Always extend the Design System.



\---



\# Long-Term Goal



SyntekERP should become a Design System, not just an ERP.



Every future module



POS



CRM



HR



Inventory



Accounting



Warehouse



Supplier Portal



Customer Portal



Mobile



must inherit the exact same visual language.



The interface should be instantly recognizable without needing to see the logo.



Consistency is more important than decoration.



Every decision should favor clarity, financial precision, performance, and long-term maintainability.

