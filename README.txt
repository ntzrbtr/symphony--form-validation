Form Validation
---------------

Version: 0.1
Author: Thomas Off (http://www.retiolum.de)
Build Date: 19.02.2009
Requirements: Symphony 2.0 or greater.


[DESCRIPTION]

This extension adds a new filter rule that adds the possibility for real form validation to Symphony.
The form validation is done by Benjamin Keen's 'PHP Validation' script (http://www.benjaminkeen.com/software/php_validation/).


[INSTALLATION]

1. Upload the 'formvalidation' folder in this archive to your Symphony 'extensions' folder.

2. Enable it at 'System' > 'Extensions'.

3. Create a new section with a textarea field where the validation rules are put in.

4. Go to 'System' > 'Preferences' and enter the name of the section and field where validation rules are stored.

5. Add the 'Form Validation' filter rule to your event via 'Blueprints' > 'Events'.

6. Save the event.

7. Create a new record in the section created in step 3 and enter the proper validation rules into the textarea.

8. Add a hidden form field containing the id of the record with the validation rules to your form.


[CHANGELOG]

Version 0.1:
- Initial release
