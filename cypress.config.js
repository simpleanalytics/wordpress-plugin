const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    // Set TEST_BASE_URL in CI/local; defaults to local WordPress.
    baseUrl: process.env.TEST_BASE_URL || 'http://localhost:8100',
    supportFile: false,
  },
});