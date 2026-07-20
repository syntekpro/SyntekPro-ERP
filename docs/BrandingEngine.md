\# SyntekERP Branding Engine



Version: 1.0



The Branding Engine is responsible for making SyntekERP a fully white-label ERP platform.



The default identity is SyntekERP, but no part of the application should depend on that name.



Every visible brand element must be configurable from the Branding Engine.



The system should never assume the application is named SyntekERP.



\---



\# Design Philosophy



SyntekERP is a platform.



Every installation may become its own product.



Examples



BusinessFlow ERP



Atlas ERP



Vertex Business Suite



SmartPOS ERP



MyCompany ERP



BlueLedger



Warehouse360



RetailSuite



The software identity belongs to the customer.



\---



\# Core Principle



Never hardcode



Application Name



Application Logo



Application Icon



Company Name



Brand Colors



Support Links



Website



Footer



Copyright



Email Signature



Receipt Header



Invoice Header



Browser Title



Login Page



Loading Screen



Everything must be generated from branding settings.



\---



\# Branding Settings



Application Name



Short Name



Application Tagline



Version



Logo Light



Logo Dark



Logo Symbol



Favicon



Touch Icon



Browser Theme Color



Primary Color



Secondary Color



Accent Color



Success Color



Warning Color



Danger Color



Navigation Color



Header Color



Background Color



Card Color



Border Color



Chart Palette



Illustration Style



Icon Pack



Rounded Corner Style



Shadow Style



Animation Style



Font Family



Number Font



RTL Enabled



Language



Timezone



Date Format



Currency



Currency Symbol



Currency Position



Website



Support Email



Support Phone



Support URL



Documentation URL



Privacy URL



Terms URL



Copyright



Footer Text



Powered By



Show Powered By



\---



\# White Label Rules



Every screen must automatically update when branding changes.



Examples



Dashboard



POS



Inventory



Warehouse



CRM



Accounting



HR



Reports



Customer Portal



Supplier Portal



Mobile



API Documentation



Emails



Invoices



Receipts



PDF Reports



Login



Loading Screen



Error Pages



Maintenance Mode



404



500



Installer



Updater



No page should contain hardcoded branding.



\---



\# Logos



Support



Horizontal Logo



Vertical Logo



Icon Only



Dark Version



Light Version



SVG



PNG



High Resolution



Print Version



Invoice Version



Receipt Version



Email Version



Logo should automatically adapt to



Light Theme



Dark Theme



Compact Navigation



Expanded Navigation



PDF



Emails



Mobile



\---



\# Icons



Support



Application Icon



Notification Icon



Touch Icon



PWA Icon



Android



iOS



Windows



macOS



Linux



Allow replacing icon packs globally.



Examples



Modern Flat



Premium 3D



Glass



Outline



Minimal



Corporate



Future versions may support custom uploaded icon packs.



\---



\# Login Experience



Allow customization of



Background



Gradient



Illustration



Animation



Video Background



Logo



Welcome Message



Company Message



Footer



Language



Theme



Wallpaper



No branding should be fixed.



\---



\# Dashboard Branding



Display



Application Name



Company Name



Branch



Logo



Current Workspace



Brand Colors



Custom Welcome Message



Dashboard Background



Wallpaper



Illustrations



Dashboard Cards



should inherit branding automatically.



\---



\# Theme Integration



Branding is separate from themes.



Example



Brand



BusinessFlow ERP



Theme



Dark Corporate



Another Brand



Atlas ERP



Theme



Light Minimal



Changing the theme should never overwrite branding.



Changing branding should never overwrite theme preferences.



\---



\# Theme Tokens



Branding Engine provides design tokens.



Examples



\--color-primary



\--color-accent



\--color-background



\--color-surface



\--color-border



\--color-success



\--color-warning



\--color-danger



\--font-primary



\--font-number



\--radius



\--shadow



Components must consume tokens.



Never use raw values.



\---



\# Typography



Branding should allow



Primary Font



Secondary Font



Monospace Font



Heading Weight



Body Weight



Number Font



Default



IBM Plex Sans



IBM Plex Mono



\---



\# Company Identity



Support



Company Name



Trade Name



Tax Number



VAT Number



Commercial Registration



Address



City



Country



Postal Code



Phone



Email



Website



Social Links



All reports should use these values automatically.



\---



\# Documents



Automatically apply branding to



Invoices



Quotations



Purchase Orders



Receipts



Delivery Notes



Credit Notes



Reports



Statements



Labels



Barcodes



QR Codes



PDF Exports



Email Templates



\---



\# Email Branding



Company Logo



Accent Color



Header



Footer



Button Style



Typography



Support Information



Social Links



Signature



\---



\# Receipt Branding



Store Logo



Store Address



VAT Number



Phone



Website



QR Code



Footer Message



Terms



Thank You Message



Receipt Width



Thermal



A4



Letter



\---



\# PDF Branding



PDF Header



PDF Footer



Watermark



Margins



Logo Position



Page Numbers



Accent Colors



Typography



Signature Area



\---



\# Browser Experience



Browser Title



Browser Theme Color



Favicon



Loading Spinner



Loading Screen



PWA Name



PWA Icon



Manifest



\---



\# White Label Levels



Level 1



Logo Only



Level 2



Logo



Colors



Company



Emails



Receipts



Level 3



Complete White Label



Everything configurable



No SyntekERP references



\---



\# Component Rules



Every component must receive branding automatically.



Example



Button



Card



Dialog



Table



Chart



Sidebar



Header



Toast



Notification



Badge



Form



Input



No component should reference brand assets directly.



\---



\# Developer Rules



Never hardcode



Text



Logo



Color



Image



URL



Icon



Everything comes from Branding Engine.



Always use



BrandContext



ThemeContext



Design Tokens



Reusable Components



\---



\# Future Features



Custom Login Themes



Seasonal Themes



Animated Logos



Multiple Brand Profiles



Per Company Branding



Agency White Label



Marketplace Themes



Theme Packs



Brand Import



Brand Export



Cloud Synchronization



Brand Templates



Brand API



\---



\# Long-Term Vision



The Branding Engine should allow a customer to transform SyntekERP into their own ERP product without modifying source code.



A user should be able to install SyntekERP today and have it appear as an entirely different product tomorrow simply by changing branding settings.



Branding must remain independent of business logic, modules, permissions, APIs, and data.



Branding changes should never require rebuilding the application.



The ERP should always feel native to the customer's organization while retaining the quality and consistency of the SyntekERP Design System.

