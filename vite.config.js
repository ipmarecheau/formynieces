export default defineConfig({
  plugins: [laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true })],
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      host: process.env.CODESPACE_NAME
        ? `${process.env.CODESPACE_NAME}-5173.app.github.dev`
        : 'localhost',
      protocol: 'wss',
      clientPort: 443,
    },
  },
});