import { defineConfig } from "vite"
import checker from "vite-plugin-checker"
import { resolve } from 'path';
// import { channel } from "diagnostics_channel";

export default defineConfig({
  root: 'src',
  publicDir: '../public',
  envDir: '../',
  base: '/',

  build: {
    outDir: '../dist',
    emptyOutDir: true,

    rollupOptions: {
      input: {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        main: resolve(__dirname, 'src/pages/index.html'),
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        401: resolve(__dirname, 'src/pages/401/index.html'),
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        login: resolve(__dirname, 'src/pages/login/index.html'),
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        logout: resolve(__dirname, 'src/pages/logout/index.html'),
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        registreer: resolve(__dirname, 'src/pages/registreer/index.html'),
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        dashboard: resolve(__dirname, 'src/dashboard/index.html'),
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        channel: resolve(__dirname, 'src/pages/channels/channels.html'),
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment,@typescript-eslint/no-unsafe-call,no-undef
        channelDetail: resolve(__dirname, 'src/pages/channels/detail.html'),

      },
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: "modern-compiler",
      },
    },
  },
  plugins: [
    checker({
      typescript: true,
      eslint: {
        lintCommand: "eslint .",
        useFlatConfig: true,
      },
      stylelint: {
        lintCommand: "stylelint src/style/**/*.{css,scss}",
      },
    }),
  ],
})