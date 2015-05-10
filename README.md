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