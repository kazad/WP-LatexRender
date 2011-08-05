<?php
/**
 * LaTeX Rendering Class - Calling function
 * Copyright (C) 2003  Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 * @author Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 * @version v0.8
 * @package latexrender
 * Revised by Steve Mayer
 * This file can be included in many PHP programs by using something like (see example.php to see how it can be used)
 * 		include_once('/full_path_here_to/latexrender/latex.php');
 * 		$text_to_be_converted=latex_content($text_to_be_converted);
 * $text_to_be_converted will then contain the link to the appropriate image
 * or an error code as follows (the 500 values can be altered in class.latexrender.php):
 * 	0 OK
 * 	1 Formula longer than 500 characters
 * 	2 Includes a blacklisted tag
 * 	3 (Not used) Latex rendering failed
 * 	4 Cannot create DVI file
 * 	5 Picture larger than 500 x 500 followed by x x y dimensions
 * 	6 Cannot copy image to pictures directory
 */

function latex_inline_content($matches) {
	$text = $matches[1];
	$orig = $matches[1];
	
	// replace a few things like pi and sqrt and cdot
	
	$text = str_replace("*", "&middot;", $text);
	$text = str_replace("\\cdot", "&middot;", $text);
	
	// replace {} with () for display, where needed
	$text = str_replace("{", "(", $text);
	$text = str_replace("}", ")", $text);
	
	// replace exponents in parens
	$text = preg_replace("#[\\^][(]([^)]+?)[)]#si", "<sup>$1</sup>", $text);
	
	// replace direct exponent
	$text = preg_replace("#[\\^](\w+)#si", "<sup>$1</sup>", $text);
	
	// remove spaces after sqrt
	$text = preg_replace("#[\\\\]sqrt\\s*#si", "&radic;", $text);

	// can be rendered with
	// $('.tex-inline').each(function(){ $(this).text('%%' + ($(this).attr('alt')) + '%%'); });
	// $('.tex').each(function(){ $(this).replaceWith('$$' + ($(this).attr('alt')) + '$$'); });
	// MathJax.Hub.Queue(["Typeset",MathJax.Hub,"DOM-ID"]);
	return '<span class="tex-inline" alt="' . $orig . '">' . $text . '</span>';
}

 function latex_content($text) {

    // --------------------------------------------------------------------------------------------------
    // adjust this to match your system configuration
    $latexrender_path = "/Applications/MAMP/htdocs/wp-content/plugins/wp-latexrender";

    $latexrender_path_http = "http://localhost/latexrender";

    // --------------------------------------------------------------------------------------------------

    include_once($latexrender_path."/class.latexrender.php");

	// $$ ... $$ becomes a normal latex element
    $text = preg_replace("#[$][$](.*?)[$][$]#si", '[tex]$1[/tex]', $text);
    $text = preg_replace_callback("#[%][%](.*?)[%][%]#si", 'latex_inline_content', $text);

    preg_match_all("#\[tex\](.*?)\[/tex\]#si",$text,$tex_matches);

    $latex = new LatexRender($latexrender_path."/pictures",$latexrender_path_http."/pictures",$latexrender_path."/tmp");

    for ($i=0; $i < count($tex_matches[0]); $i++) {
        $pos = strpos($text, $tex_matches[0][$i]);
        $latex_formula = $tex_matches[1][$i];

	// wrap in \displaystyle{...} by default
	$latex_formula = "\\displaystyle{" . $latex_formula . "}";

	// prevent WordPress changing & and ' to 038; and 8217;
	// add more such lines to avoid other odd effects
	$latex_formula = str_replace("&#038;","&",$latex_formula);
	$latex_formula = str_replace("&#8217;","'",$latex_formula);

	// if you use htmlArea to input the text then uncomment the next 6 lines
	//	$latex_formula = str_replace("&amp;","&",$latex_formula);
	//	$latex_formula = str_replace("&#38;","&",$latex_formula);
	//	$latex_formula = str_replace("&nbsp;"," ",$latex_formula);
	//	$latex_formula = str_replace("<BR>","",$latex_formula);
	//	$latex_formula = str_replace("<P>","",$latex_formula);
	//	$latex_formula = str_replace("</P>","",$latex_formula);

        $url = $latex->getFormulaURL($latex_formula);

		$alt_latex_formula = htmlentities($latex_formula, ENT_QUOTES);
		$alt_latex_formula = str_replace("\r","&#13;",$alt_latex_formula);
		$alt_latex_formula = str_replace("\n","&#10;",$alt_latex_formula);

        if ($url != false) {
            $text = substr_replace($text, "<img src='".$url."' title='".$alt_latex_formula."' alt='".$alt_latex_formula."' align=absmiddle class='tex'>",$pos,strlen($tex_matches[0][$i]));
        } else {
            $text = substr_replace($text, "[Unparseable or potentially dangerous latex formula. Error $latex->_errorcode $latex->_errorextra]",$pos,strlen($tex_matches[0][$i]));
        }
    }
    return $text;
}

?>
