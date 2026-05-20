describe('Simple Analytics Final Verification', () => {
  
  beforeEach(() => {
    // Phase 2: Handle login to ensure stability and avoid 403 errors
    cy.visit('/wp-login.php');
    cy.get('#user_login').type('admin'); 
    cy.get('#user_pass').type('kQuWVcpdEFU'); 
    cy.get('#wp-submit').click();
  });

  it('Verifies Phase 1: All Bug Fixes and UI', () => {
    // 1. Navigate directly to the plugin settings
    cy.visit('/wp-admin/admin.php?page=simpleanalytics');
    
    // 2. Success Criteria: Verify UI Clarity (Issue #8)
    // We check for the specific privacy text you added to simple-analytics.php
    cy.get('body').should('contain', 'Simple Analytics');
    cy.contains('GDPR-compliant').should('be.visible');

    // 3. Success Criteria: Verify Saving Settings
    // We use a broader selector for the input to ensure it finds it
    cy.get('input').filter('[name*="custom_domain"]').should('exist').clear().type('scripts.example.com');
    cy.get('button[type="submit"]').first().click();
    
    // 4. Verification of successful save
    cy.get('body').should('contain', 'Settings saved');
  });

  it('Verifies Phase 1: Script Injection Quality', () => {
    cy.visit('/');
    
    // Issue #6: Verify the data-platform attribute is correctly injected
    cy.get('script[data-platform="wordpress"]').should('exist');
    
    // Issue #11: Verify identifying comment prefix
    cy.document().then((doc) => {
      const html = doc.documentElement.innerHTML;
      expect(html).to.contain('');
    });
  });
});