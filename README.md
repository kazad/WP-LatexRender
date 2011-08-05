Overview
========

I've been using [Latex Render](http://sixthform.info/steve/wordpress/) for my Wordpress blog, but want to try MathJax (javascript math rendering). This is a replacement for the latex.php engine.

Problem: switching to MathJax breaks RSS and email!

Solution:

* Keep wp-latexrender as normal. All content between [tex]...[/tex] is converted to an image, RSS and email friendly.
* Tag generated images with a class ('tex'). The "alt" attribute is the original Latex.
* Convert the images back into MathJax with javascript:

    $('.tex').each(function(){ $(this).replaceWith('$$' + ($(this).attr('alt')) + '$$'); });

* Manually run MathJax to convert the images back into its format [fits better, font scaling, etc.]

Bonus!

* You can use $$ ... $$ (my preferred mathjax settings) for displayed equations, no need for [tex]...[/tex]
* You can use %% ... %% (my settings) for inline equations. Currently, inline is "converted" to HTML. So simple things like exponents become "sup" tags, \sqrt becomes the html entity, etc. These are wrapped in a


    <span class="tex-inline" alt="(original formula)">...my HTML conversion... </span>


and you can convert this to real inline MathJax with:

    $('.tex-inline').each(function(){ $(this).text('%%' + ($(this).attr('alt')) + '%%'); });