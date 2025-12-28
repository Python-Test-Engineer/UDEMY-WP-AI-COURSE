
1. Fresh install of WordPres
2. Install GeneratePress Theme
3. Install  COURSE-2-WP-PLUGINS\01_CREATE_CHILD_THEME.zip
    Appearance > Child Theme create 'gen-child'.
    
    OPTIONAL: Add this CSS in style.css or customizer

    ```
    svg {
    width: 50px;
    height: 50px;
    }
    img {
    width: 50px;
    height: 50px;
    }
    ```

4. Upload `COURSE-2-WP-PLUGINS\CUSTOM-WP-PAGES\page-basic-agent.php` > Child Theme
5. TOOLS > WP Importer for `COURSE-2-WP-PLUGINS\WP-EXPORTER\UDEMY-WP-AI-EXPORT.xml ` for posts and any pages etc. 
6. Change Site title as needed and customizer > layout > container width to 1400px (optional)
7. Settings > Media uncheck and say why...
8. Install `COURSE-2-WP-PLUGINS\03_PREFIX_CHANGE.zip` to make prefix wp_ (just in case) but $wpdb->prefix used for tables. TOOLS > DB PREFIX
9. Settings > Wider Menu plugin `COURSE-2-WP-PLUGINS\04_WIDER_MENU.zip`
10. Add plugins and test admin and frontend.

We will load in PLUGINS 04+ as needed.