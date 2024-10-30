# Galleries

The lightbox carousel template currently requires Bootstrao to exposed as a global variable named `bootstrap`. 
Add the following code to your esm script.

```js
import * as bootstrap from "bootstrap";

window.bootstrap = bootstrap;
```