CPT Maker
-----------
WordPress plugin to help quickly create and initialize custom post types and associated taxonomies.

Use
----
- Helper functions to quickly register post types and taxonomies with little configuration
- Use cpt_register_post_type(...) and cpt_register_taxonomy(...) unless advanced configuration is needed
        - Plural option for each allows override of plural name in common situations; e.g. case-study will become plural "Case Studys" unless you register "Case Studies" as the plural 
- CPTMaker\Registration factory allows granular registration of either content type and association, avoiding duplicate registration of post types and taxonomies
- Two ways to activate: Add as a must use plugin and use immediately, or add as a regular plugin and activate via the hook 'cpt-registration'

v1.0.0
-------
- Very basic functionality, creates the base CPT and taxonomies

v1.1.0
------
- Multiple misnamed array indexes fixed
- Add missing version to _doing_it_wrong call
- Update register() closure to support 5.3/5.4 and register all associated taxonomies
- Add a taxonomy filter to associated post type listings

v2.0.0
------
- Switch to namespace based classes
- Auto-loader added based on directory structure
- Action hooks added during 'plugins_loaded' which allows the integrator to add custom post types during the hook 'cpt-registration'