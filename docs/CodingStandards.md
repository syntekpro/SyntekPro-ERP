\# SyntekERP Coding Standards



\## Current Development Phase



Current Phase:



🎨 UI/UX \& Branding



This phase is strictly focused on the visual redesign of SyntekERP.



Business logic is considered stable unless explicitly requested.



\---



\## AI Development Rules



When generating or modifying code:



DO



\- Improve UI and UX.

\- Create reusable components.

\- Improve layouts.

\- Improve responsiveness.

\- Improve accessibility.

\- Improve typography.

\- Improve spacing.

\- Improve animations.

\- Improve icons.

\- Improve dashboard experience.

\- Improve navigation.

\- Improve theming.

\- Improve branding.

\- Refactor duplicated UI into reusable components.

\- Use existing design tokens.

\- Follow the DesignInstructions.md document.

\- Follow BrandingEngine.md.



DO NOT



\- Modify business logic.

\- Change API endpoints.

\- Change authentication.

\- Change authorization.

\- Change permissions.

\- Change routes.

\- Change middleware.

\- Change database schema.

\- Rename backend models.

\- Modify controllers.

\- Change services.

\- Change repositories.

\- Change migrations.

\- Change module architecture.



Unless specifically instructed.



\---



\## Component Rules



Always reuse existing components.



If a component does not exist:



1\. Create a reusable component.



2\. Add it to the shared component library.



3\. Use it throughout the application.



Never duplicate components.



\---



\## UI First



Before creating new functionality ask:



Can this be solved by improving an existing component?



If yes,



Improve the component.



Do not duplicate UI.



\---



\## Design Authority



The following documents define the UI.



DesignInstructions.md



BrandingEngine.md



Every generated page must follow those specifications.



\---



Current Priority



★★★★★ Design



★★★★★ Branding



★★★★★ UX



★★★★★ Accessibility



★★★★☆ Components



★★★☆☆ Performance



☆☆☆☆☆ Backend Refactoring



Business logic is out of scope during this phase.

