# modsassc
Prestashop module to use SASS language in your front-office
 
##How to use
To use the module you just install it and hook in the *displayHeader* position.
Then go to the configuration page and you'll be presented with two windows: the editor and the viewer:

 - the **editor** is where you write your SCSS styles
 - the **viewer** is where the compiled CSS is shown (not editable)
 
 Just hit *Save* and you're done!
 
##Importing library
You can import your own SASS libraries, too! Just upload them in the *scss/source* folder in the module's installation folder and include them. Bourbon is included (partly) as an example.
 
##External dependencies
modsassc uses:
  - <a href="http://codemirror.net">CodeMirror</a> as editor
  - <a href="http://leafo.net/scssphp/">SCSSPHP</a> v0.0.12 as SASS compiler (implements SASS 3.2.12)
  - <a href="http://bourbon.io">Bourbon</a> v4.2.3 as example library</a>
