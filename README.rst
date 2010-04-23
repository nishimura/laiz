====================
Laiz: PHP Framework
====================

Laiz is MVC framework that is developed with PHP5, and uses HTML_Template_Flexy.

Laiz is a framework to offer a flexible development environment.
Current is a development version.


Usage
=====
Adding ``compiled`` directory in project directory::

   cd project_dir
   mkdir compiled
   chmod o+w compiled

Including ``Laiz.php`` and invoke ``laze`` method to run framework::

   mkdir htdocs
   cat > htdocs/index.php
   <?php
   require_once 'path_to_laiz/Laiz.php';
   Laiz::laze();

Adding template file of top page::

   mkdir -p app/Base/templates
   echo 'Hello World!' > components/Base/templates/Top.html

Adding template files with action::

   TODO

