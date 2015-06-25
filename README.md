CPT Maker
-----------

WordPress plugin to help quickly create and initialize custom post types and associated taxonomies.

Use
----
- Create a new instance of CPT_Maker for each new CPT.
- __construct uses a $name string or array with $delimiter for the key value and override for standard $labels and $arguments CPT arrays
- Add new taxonomies with add_taxonomy()
- After creating the CPT and any taxonomies, call register() to add standard WordPress actions

v1.0.0
-------
- Very basic functionality, creates the base CPT and taxonomies

v1.1.0
------
- Multiple misnamed array indexes fixed
- Add missing version to _doing_it_wrong call
- Update register() closure to support 5.3/5.4 and register all associated taxonomies
- Add a taxonomy filter to associated post type listings