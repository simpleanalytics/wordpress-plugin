const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    // REPLACE THIS with your actual TasteWP or Localhost URL
    baseUrl: 'https://shaggyroll.s3-tastewp.com', 
    supportFile: false,
  },
});