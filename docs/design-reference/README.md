# Home page design reference

Zina's approved React prototype for the public Home page (`/`) and Help center (`/help`). It is the visual and content target for the Blade pages in `resources/views/home/`. Nothing in this folder is built or loaded by the app.

| File | What it is |
| --- | --- |
| `src/pages/home/HomePage.tsx` | Home page markup and content |
| `src/pages/home/HelpCenter.tsx` | Help center: article search and the report form |
| `src/pages/home/HomeIcon.tsx` | The stroke icons both pages use |
| `src/pages/home/home.css` | Home and Help styles, all scoped under `.hp` |

The prototype's global `index.css` and `refinement.css` are not copied here. They style the whole authenticated prototype app, and the Home page does not depend on them: `home.css` defines its own colours, spacing and type, and its last block only undoes `refinement.css` font overrides.

## How the Blade version differs

- Colours, radii and shadows come from `resources/css/tokens.css`, and buttons, badges and form fields use the shared Blade components.
- Page content (steps, project types, roles, FAQ, help articles) lives in `config/milestone_home.php`.
- The report form posts to Laravel (`POST /help/reports`) and works without JavaScript. The prototype's "simulate a failed send" checkbox is not included.
- Sign in, Get started and Go to dashboard link to the auth routes once they exist.
