import globals from "globals"
import pluginJs from "@eslint/js"
import tseslint from "typescript-eslint"

export default tseslint.config(
  { ignores: ["dist"] },
  { files: ["**/*.{js,mjs,cjs,ts}"] },
  pluginJs.configs.recommended,
  tseslint.configs.recommendedTypeChecked,
  {
    languageOptions: {
      globals: globals.browser,
      parserOptions: {
        project: true,
        projectService: {
          allowDefaultProject: ["eslint.config.js", "vite.config.js"],
        },
      },
    },
  },
  {
    rules: {
      // Require explicit return and argument types on
      // exported functions' and classes' public class methods.
      "@typescript-eslint/explicit-module-boundary-types": "error",
    },
  },
)
