<?php
#
# Markdown  -  A text-to-HTML conversion tool for web writers
#
# PHP Markdown
# Copyright (c) 2004-2012 Michel Fortin  
# <http://michelf.com/projects/php-markdown/>
#
# Original Markdown
# Copyright (c) 2004-2006 John Gruber  
# <http://daringfireball.net/projects/markdown/>
#


define( 'MARKDOWN_VERSION',  "1.0.1o" ); # Sun 8 Jan 2012


#
# Global default settings:
#

# Change to ">" for HTML output
@define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX',  " />");

# Define the width of a tab for code blocks.
@define( 'MARKDOWN_TAB_WIDTH',     4 );


#
# WordPress settings:
#

# Change to false to remove Markdown from posts and/or comments.
@define( 'MARKDOWN_WP_POSTS',      true );
@define( 'MARKDOWN_WP_COMMENTS',   true );


### WordPress Plugin Interface ###

/*
Plugin Name: Markdown
Plugin URI: http://michelf.com/projects/php-markdown/
Description: <a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> allows you to write using an easy-to-read, easy-to-write plain text format. Based on the original Perl version by <a href="http://daringfireball.net/">John Gruber</a>. <a href="http://michelf.com/projects/php-markdown/">More...</a>
Version: 1.0.1o
Author: Michel Fortin
Author URI: http://michelf.com/
*/

if (isset($wp_version)) {
	# More details about how it works here:
	# <http://michelf.com/weblog/2005/wordpress-text-flow-vs-markdown/>
	
	# Post content and excerpts
	# - Remove WordPress paragraph generator.
	# - Run Markdown on excerpt, then remove all tags.
	# - Add paragraph tag around the excerpt, but remove it for the excerpt rss.
	if (MARKDOWN_WP_POSTS) {
		remove_filter('the_content',     'wpautop');
        remove_filter('the_content_rss', 'wpautop');
		remove_filter('the_excerpt',     'wpautop');
		add_filter('the_content',     'Markdown', 6);
        add_filter('the_content_rss', 'Markdown', 6);
		add_filter('get_the_excerpt', 'Markdown', 6);
		add_filter('get_the_excerpt', 'trim', 7);
		add_filter('the_excerpt',     'mdwp_add_p');
		add_filter('the_excerpt_rss', 'mdwp_strip_p');
		
		remove_filter('content_save_pre',  'balanceTags', 50);
		remove_filter('excerpt_save_pre',  'balanceTags', 50);
		add_filter('the_content',  	  'balanceTags', 50);
		add_filter('get_the_excerpt', 'balanceTags', 9);
	}
	
	# Comments
	# - Remove WordPress paragraph generator.
	# - Remove WordPress auto-link generator.
	# - Scramble important tags before passing them to the kses filter.
	# - Run Markdown on excerpt then remove paragraph tags.
	if (MARKDOWN_WP_COMMENTS) {
		remove_filter('comment_text', 'wpautop', 30);
		remove_filter('comment_text', 'make_clickable');
		add_filter('pre_comment_content', 'Markdown', 6);
		add_filter('pre_comment_content', 'mdwp_hide_tags', 8);
		add_filter('pre_comment_content', 'mdwp_show_tags', 12);
		add_filter('get_comment_text',    'Markdown', 6);
		add_filter('get_comment_excerpt', 'Markdown', 6);
		add_filter('get_comment_excerpt', 'mdwp_strip_p', 7);
	
		global $mdwp_hidden_tags, $mdwp_placeholders;
		$mdwp_hidden_tags = explode(' ',
			'<p> </p> <pre> </pre> <ol> </ol> <ul> </ul> <li> </li>');
		$mdwp_placeholders = explode(' ', str_rot13(
			'pEj07ZbbBZ U1kqgh4w4p pre2zmeN6K QTi31t9pre ol0MP1jzJR '.
			'ML5IjmbRol ulANi1NsGY J7zRLJqPul liA8ctl16T K9nhooUHli'));
	}
	
	function mdwp_add_p($text) {
		if (!preg_match('{^$|^<(p|ul|ol|dl|pre|blockquote)>}i', $text)) {
			$text = '<p>'.$text.'</p>';
			$text = preg_replace('{\n{2,}}', "</p>\n\n<p>", $text);
		}
		return $text;
	}
	
	function mdwp_strip_p($t) { return preg_replace('{</?p>}i', '', $t); }

	function mdwp_hide_tags($text) {
		global $mdwp_hidden_tags, $mdwp_placeholders;
		return str_replace($mdwp_hidden_tags, $mdwp_placeholders, $text);
	}
	function mdwp_show_tags($text) {
		global $mdwp_hidden_tags, $mdwp_placeholders;
		return str_replace($mdwp_placeholders, $mdwp_hidden_tags, $text);
	}
}


### bBlog Plugin Info ###

function identify_modifier_markdown() {
	return array(
		'name'			=> 'markdown',
		'type'			=> 'modifier',
		'nicename'		=> 'Markdown',
		'description'	=> 'A text-to-HTML conversion tool for web writers',
		'authors'		=> 'Michel Fortin and John Gruber',
		'licence'		=> 'BSD-like',
		'version'		=> MARKDOWN_VERSION,
		'help'			=> '<a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> allows you to write using an easy-to-read, easy-to-write plain text format. Based on the original Perl version by <a href="http://daringfireball.net/">John Gruber</a>. <a href="http://michelf.com/projects/php-markdown/">More...</a>'
	);
}


### Smarty Modifier Interface ###

function smarty_modifier_markdown($text) {
	return Markdown($text);
}


### Textile Compatibility Mode ###

# Rename this file to "classTextile.php" and it can replace Textile everywhere.

if (strcasecmp(substr(__FILE__, -16), "classTextile.php") == 0) {
	# Try to include PHP SmartyPants. Should be in the same directory.
	@include_once 'smartypants.php';
	# Fake Textile class. It calls Markdown instead.
	class Textile {
		function TextileThis($text, $lite='', $encode='') {
			if ($lite == '' && $encode == '')    $text = Markdown($text);
			if (function_exists('SmartyPants'))  $text = SmartyPants($text);
			return $text;
		}
		# Fake restricted version: restrictions are not supported for now.
		function TextileRestricted($text, $lite='', $noimage='') {
			return $this->TextileThis($text, $lite);
		}
		# Workaround to ensure compatibility with TextPattern 4.0.3.
		function blockLite($text) { return $text; }
	}
}



#
# Markdown Parser Class
#

class Markdown_Parser {

	# Regex to match balanced [brackets].
	# Needed to insert a maximum bracked depth while converting to PHP.
	var $nested_brackets_depth = 6;
	var $nested_brackets_re;
	
	var $nested_url_parenthesis_depth = 4;
	var $nested_url_parenthesis_re;

	# Table of hash values for escaped characters:
	var $escape_chars = '\`*_{}[]()>#+-.!';
	var $escape_chars_re;

	# Change to ">" for HTML output.
	var $empty_element_suffix = MARKDOWN_EMPTY_ELEMENT_SUFFIX;
	var $tab_width = MARKDOWN_TAB_WIDTH;
	
	# Change to `true` to disallow markup or entities.
	var $no_markup = false;
	var $no_entities = false;
	
	# Predefined urls and titles for reference links and images.
	var $predef_urls = array();
	var $predef_titles = array();


	function Markdown_Parser() {
	#
	# Constructor function. Initialize appropriate member variables.
	#
		$this->_initDetab();
		$this->prepareItalicsAndBold();
	
		$this->nested_brackets_re = 
			str_repeat('(?>[^\[\]]+|\[', $this->nested_brackets_depth).
			str_repeat('\])*', $this->nested_brackets_depth);
	
		$this->nested_url_parenthesis_re = 
			str_repeat('(?>[^()\s]+|\(', $this->nested_url_parenthesis_depth).
			str_repeat('(?>\)))*', $this->nested_url_parenthesis_depth);
		
		$this->escape_chars_re = '['.preg_quote($this->escape_chars).']';
		
		# Sort document, block, and span gamut in ascendent priority order.
		asort($this->document_gamut);
		asort($this->block_gamut);
		asort($this->span_gamut);
	}


	# Internal hashes used during transformation.
	var $urls = array();
	var $titles = array();
	var $html_hashes = array();
	
	# Status flag to avoid invalid nesting.
	var $in_anchor = false;
	
	
	function setup() {
	#
	# Called before the transformation process starts to setup parser 
	# states.
	#
		# Clear global hashes.
		$this->urls = $this->predef_urls;
		$this->titles = $this->predef_titles;
		$this->html_hashes = array();
		
		$in_anchor = false;
	}
	
	function teardown() {
	#
	# Called after the transformation process to clear any variable 
	# which may be taking up memory unnecessarly.
	#
		$this->urls = array();
		$this->titles = array();
		$this->html_hashes = array();
	}


	function transform($text) {
	#
	# Main function. Performs some preprocessing on the input text
	# and pass it through the document gamut.
	#
		$this->setup();
	
		# Remove UTF-8 BOM and marker character in input, if present.
		$text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);

		# Standardize line endings:
		#   DOS to Unix and Mac to Unix
		$text = preg_replace('{\r\n?}', "\n", $text);

		# Make sure $text ends with a couple of newlines:
		$text .= "\n\n";

		# Convert all tabs to spaces.
		$text = $this->detab($text);

		# Turn block-level HTML blocks into hash entries
		$text = $this->hashHTMLBlocks($text);

		# Strip any lines consisting only of spaces and tabs.
		# This makes subsequent regexen easier to write, because we can
		# match consecutive blank lines with /\n+/ instead of something
		# contorted like /[ ]*\n+/ .
		$text = preg_replace('/^[ ]+$/m', '', $text);

		# Run document gamut methods.
		foreach ($this->document_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		
		$this->teardown();

		return $text . "\n";
	}
	
	var $document_gamut = array(
		# Strip link definitions, store in hashes.
		"stripLinkDefinitions" => 20,
		
		"runBasicBlockGamut"   => 30,
		);


	function stripLinkDefinitions($text) {
	#
	# Strips link definitions from text, stores the URLs and titles in
	# hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: ^[id]: url "optional title"
		$text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>			# url = $2
							|
							  (\S+?)			# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	# title is optional
							(?:\n+|\Z)
			}xm',
			array(&$this, '_stripLinkDefinitions_callback'),
			$text);
		return $text;
	}
	function _stripLinkDefinitions_callback($matches) {
		$link_id = strtolower($matches[1]);
		$url = $matches[2] == '' ? $matches[3] : $matches[2];
		$this->urls[$link_id] = $url;
		$this->titles[$link_id] =& $matches[4];
		return ''; # String that will replace the block
	}


	function hashHTMLBlocks($text) {
		if ($this->no_markup)  return $text;

		$less_than_tab = $this->tab_width - 1;

		# Hashify HTML blocks:
		# We only want to do this for block-level HTML tags, such as headers,
		# lists, and tables. That's because we still want to wrap <p>s around
		# "paragraphs" that are wrapped in non-block-level tags, such as anchors,
		# phrase emphasis, and spans. The list of tags we're looking for is
		# hard-coded:
		#
		# *  List "a" is made of tags which can be both inline or block-level.
		#    These will be treated block-level when the start tag is alone on 
		#    its line, otherwise they're not matched here and will be taken as 
		#    inline later.
		# *  List "b" is made of tags which are always block-level;
		#
		$block_tags_a_re = 'ins|del';
		$block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'.
						   'script|noscript|form|fieldset|iframe|math';

		# Regular expression for the content of a block tag.
		$nested_tags_level = 4;
		$attr = '
			(?>				# optional tag attributes
			  \s			# starts with whitespace
			  (?>
				[^>"/]+		# text outside quotes
			  |
				/+(?!>)		# slash not followed by ">"
			  |
				"[^"]*"		# text inside double quotes (tolerate ">")
			  |
				\'[^\']*\'	# text inside single quotes (tolerate ">")
			  )*
			)?	
			';
		$content =
			str_repeat('
				(?>
				  [^<]+			# content without tag
				|
				  <\2			# nested opening tag
					'.$attr.'	# attributes
					(?>
					  />
					|
					  >', $nested_tags_level).	# end of opening tag
					  '.*?'.					# last level nested tag content
			str_repeat('
					  </\2\s*>	# closing nested tag
					)
				  |				
					<(?!/\2\s*>	# other tags with a different name
				  )
				)*',
				$nested_tags_level);
		$content2 = str_replace('\2', '\3', $content);

		# First, look for nested blocks, e.g.:
		# 	<div>
		# 		<div>
		# 		tags for inner block must be indented.
		# 		</div>
		# 	</div>
		#
		# The outermost tags must start at the left margin for this to match, and
		# the inner nested divs must be indented.
		# We need to do this before the next, more liberal match, because the next
		# match will start at the first `<div>` and stop at the first `</div>`.
		$text = preg_replace_callback('{(?>
			(?>
				(?<=\n\n)		# Starting after a blank line
				|				# or
				\A\n?			# the beginning of the doc
			)
			(						# save in $1

			  # Match from `\n<tag>` to `</tag>\n`, handling nested tags 
			  # in between.
					
						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_b_re.')# start tag = $2
						'.$attr.'>			# attributes followed by > and \n
						'.$content.'		# content, support nesting
						</\2>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document

			| # Special version for tags of group a.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_a_re.')# start tag = $3
						'.$attr.'>[ ]*\n	# attributes followed by >
						'.$content2.'		# content, support nesting
						</\3>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document
					
			| # Special case just for <hr />. It was easier to make a special 
			  # case than to make the other regex more complicated.
			
						[ ]{0,'.$less_than_tab.'}
						<(hr)				# start tag = $2
						'.$attr.'			# attributes
						/?>					# the matching end tag
						[ ]*
						(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # Special case for standalone HTML comments:
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<!-- .*? -->
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # PHP and ASP-style processor instructions (<? and <%)
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<([?%])			# $2
						.*?
						\2>
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
					
			)
			)}Sxmi',
			array(&$this, '_hashHTMLBlocks_callback'),
			$text);

		return $text;
	}
	function _hashHTMLBlocks_callback($matches) {
		$text = $matches[1];
		$key  = $this->hashBlock($text);
		return "\n\n$key\n\n";
	}
	
	
	function hashPart($text, $boundary = 'X') {
	#
	# Called whenever a tag must be hashed when a function insert an atomic 
	# element in the text stream. Passing $text to through this function gives
	# a unique text-token which will be reverted back when calling unhash.
	#
	# The $boundary argument specify what character should be used to surround
	# the token. By convension, "B" is used for block elements that needs not
	# to be wrapped into paragraph tags at the end, ":" is used for elements
	# that are word separators and "X" is used in the general case.
	#
		# Swap back any tag hash found in $text so we do not have to `unhash`
		# multiple times at the end.
		$text = $this->unhash($text);
		
		# Then hash the block.
		static $i = 0;
		$key = "$boundary\x1A" . ++$i . $boundary;
		$this->html_hashes[$key] = $text;
		return $key; # String that will replace the tag.
	}


	function hashBlock($text) {
	#
	# Shortcut function for hashPart with block-level boundaries.
	#
		return $this->hashPart($text, 'B');
	}


	var $block_gamut = array(
	#
	# These are all the transformations that form block-level
	# tags like paragraphs, headers, and list items.
	#
		"doHeaders"         => 10,
		"doHorizontalRules" => 20,
		
		"doLists"           => 40,
		"doCodeBlocks"      => 50,
		"doBlockQuotes"     => 60,
		);

	function runBlockGamut($text) {
	#
	# Run block gamut tranformations.
	#
		# We need to escape raw HTML in Markdown source before doing anything 
		# else. This need to be done for each block, and not only at the 
		# begining in the Markdown function since hashed blocks can be part of
		# list items and could have been indented. Indented blocks would have 
		# been seen as a code block in a previous pass of hashHTMLBlocks.
		$text = $this->hashHTMLBlocks($text);
		
		return $this->runBasicBlockGamut($text);
	}
	
	function runBasicBlockGamut($text) {
	#
	# Run block gamut tranformations, without hashing HTML blocks. This is 
	# useful when HTML blocks are known to be already hashed, like in the first
	# whole-document pass.
	#
		foreach ($this->block_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		
		# Finally form paragraph and restore hashed blocks.
		$text = $this->formParagraphs($text);

		return $text;
	}
	
	
	function doHorizontalRules($text) {
		# Do Horizontal Rules:
		return preg_replace(
			'{
				^[ ]{0,3}	# Leading space
				([-*_])		# $1: First marker
				(?>			# Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			# Marker character
				){2,}		# Group repeated at least twice
				[ ]*		# Tailing spaces
				$			# End of line.
			}mx',
			"\n".$this->hashBlock("<hr$this->empty_element_suffix")."\n", 
			$text);
	}


	var $span_gamut = array(
	#
	# These are all the transformations that occur *within* block-level
	# tags like paragraphs, headers, and list items.
	#
		# Process character escapes, code spans, and inline HTML
		# in one shot.
		"parseSpan"           => -30,

		# Process anchor and image tags. Images must come first,
		# because ![foo][f] looks like an anchor.
		"doImages"            =>  10,
		"doAnchors"           =>  20,
		
		# Make links out of things like `<http://example.com/>`
		# Must come after doAnchors, because you can use < and >
		# delimiters in inline links like [this](<url>).
		"doAutoLinks"         =>  30,
		"encodeAmpsAndAngles" =>  40,

		"doItalicsAndBold"    =>  50,
		"doHardBreaks"        =>  60,
		);

	function runSpanGamut($text) {
	#
	# Run span gamut tranformations.
	#
		foreach ($this->span_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		return $text;
	}
	
	
	function doHardBreaks($text) {
		# Do hard breaks:
		return preg_replace_callback('/ {2,}\n/', 
			array(&$this, '_doHardBreaks_callback'), $text);
	}
	function _doHardBreaks_callback($matches) {
		return $this->hashPart("<br$this->empty_element_suffix\n");
	}


	function doAnchors($text) {
	#
	# Turn Markdown link shortcuts into XHTML <a> tags.
	#
		if ($this->in_anchor) return $text;
		$this->in_anchor = true;
		
		#
		# First, handle reference-style links: [link text] [id]
		#
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs',
			array(&$this, '_doAnchors_reference_callback'), $text);

		#
		# Next, inline-style links: [link text](url "optional title")
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					('.$this->nested_url_parenthesis_re.')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			)
			}xs',
			array(&$this, '_doAnchors_inline_callback'), $text);

		#
		# Last, handle reference-style shortcuts: [link text]
		# These must come last in case you've also got [link text][1]
		# or [link text](/foo)
		#
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs',
			array(&$this, '_doAnchors_reference_callback'), $text);

		$this->in_anchor = false;
		return $text;
	}
	function _doAnchors_reference_callback($matches) {
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == "") {
			# for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}
		
		# lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			$url = $this->encodeAttribute($url);
			
			$result = "<a href=\"$url\"";
			if ( isset( $this->titles[$link_id] ) ) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}
		
			$link_text = $this->runSpanGamut($link_text);
			$result .= ">$link_text</a>";
			$result = $this->hashPart($result);
		}
		else {
			$result = $whole_match;
		}
		return $result;
	}
	function _doAnchors_inline_callback($matches) {
		$whole_match	=  $matches[1];
		$link_text		=  $this->runSpanGamut($matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$url = $this->encodeAttribute($url);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\"";
		}
		
		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}


	function doImages($text) {
	#
	# Turn Markdown image shortcuts into <img> tags.
	#
		#
		# First, handle reference-style labeled images: ![alt text][id]
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs', 
			array(&$this, '_doImages_reference_callback'), $text);

		#
		# Next, handle inline images:  ![alt text](url "optional title")
		# Don't forget: encode * and _
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			)
			}xs',
			array(&$this, '_doImages_inline_callback'), $text);

		return $text;
	}
	function _doImages_reference_callback($matches) {
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == "") {
			$link_id = strtolower($alt_text); # for shortcut links like ![this][].
		}

		$alt_text = $this->encodeAttribute($alt_text);
		if (isset($this->urls[$link_id])) {
			$url = $this->encodeAttribute($this->urls[$link_id]);
			$result = "<img src=\"$url\" alt=\"$alt_text\"";
			if (isset($this->titles[$link_id])) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}
			$result .= $this->empty_element_suffix;
			$result = $this->hashPart($result);
		}
		else {
			# If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}
	function _doImages_inline_callback($matches) {
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeAttribute($url);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\""; # $title already quoted
		}
		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}


	function doHeaders($text) {
		# Setext-style headers:
		#	  Header 1
		#	  ========
		#  
		#	  Header 2
		#	  --------
		#
		$text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
			array(&$this, '_doHeaders_callback_setext'), $text);

		# atx-style headers:
		#	# Header 1
		#	## Header 2
		#	## Header 2 with closing hashes ##
		#	...
		#	###### Header 6
		#
		$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
	function _doHeaders_callback_setext($matches) {
		# Terrible hack to check we haven't found an empty list item.
		if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1]))
			return $matches[0];
		
		$level = $matches[2]{0} == '=' ? 1 : 2;
		$block = "<h$level>".$this->runSpanGamut($matches[1])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	function _doHeaders_callback_atx($matches) {
		$level = strlen($matches[1]);
		$block = "<h$level>".$this->runSpanGamut($matches[2])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}


	function doLists($text) {
	#
	# Form HTML ordered (numbered) and unordered (bulleted) lists.
	#
		$less_than_tab = $this->tab_width - 1;

		# Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

		$markers_relist = array(
			$marker_ul_re => $marker_ol_re,
			$marker_ol_re => $marker_ul_re,
			);

		foreach ($markers_relist as $marker_re => $other_marker_re) {
			# Re-usable pattern to match any entirel ul or ol list:
			$whole_list_re = '
				(								# $1 = whole list
				  (								# $2
					([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
					('.$marker_re.')			# $4 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								# $5
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						# Negative lookahead for another list item marker
						[ ]*
						'.$marker_re.'[ ]+
					  )
					|
					  (?=						# Lookahead for another kind of list
					    \n
						\3						# Must have the same indentation
						'.$other_marker_re.'[ ]+
					  )
				  )
				)
			'; // mx
			
			# We use a different prefix before nested lists than top-level lists.
			# See extended comment in _ProcessListItems().
		
			if ($this->list_level) {
				$text = preg_replace_callback('{
						^
						'.$whole_list_re.'
					}mx',
					array(&$this, '_doLists_callback'), $text);
			}
			else {
				$text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list_re.'
					}mx',
					array(&$this, '_doLists_callback'), $text);
			}
		}

		return $text;
	}
	function _doLists_callback($matches) {
		# Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";
		
		$list = $matches[1];
		$list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul" : "ol";
		
		$marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re );
		
		$list .= "\n";
		$result = $this->processListItems($list, $marker_any_re);
		
		$result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>");
		return "\n". $result ."\n\n";
	}

	var $list_level = 0;

	function processListItems($list_str, $marker_any_re) {
	#
	#	Process the contents of a single ordered or unordered list, splitting it
	#	into individual list items.
	#
		# The $this->list_level global keeps track of when we're inside a list.
		# Each time we enter a list, we increment it; when we leave a list,
		# we decrement. If it's zero, we're not in a list anymore.
		#
		# We do this because when we're not inside a list, we want to treat
		# something like this:
		#
		#		I recommend upgrading to version
		#		8. Oops, now this line is treated
		#		as a sub-list.
		#
		# As a single paragraph, despite the fact that the second line starts
		# with a digit-period-space sequence.
		#
		# Whereas when we're inside a list (or sub-list), that line will be
		# treated as the start of a sub-list. What a kludge, huh? This is
		# an aspect of Markdown's syntax that's hard to parse perfectly
		# without resorting to mind-reading. Perhaps the solution is to
		# change the syntax rules such that sub-lists must start with a
		# starting cardinal number; e.g. "1." or "a.".
		
		$this->list_level++;

		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?							# leading line = $1
			(^[ ]*)							# leading whitespace = $2
			('.$marker_any_re.'				# list marker and space = $3
				(?:[ ]+|(?=\n))	# space only required if item is not empty
			)
			((?s:.*?))						# list item text   = $4
			(?:(\n+(?=\n))|\n)				# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
			}xm',
			array(&$this, '_processListItems_callback'), $list_str);

		$this->list_level--;
		return $list_str;
	}
	function _processListItems_callback($matches) {
		$item = $matches[4];
		$leading_line =& $matches[1];
		$leading_space =& $matches[2];
		$marker_space = $matches[3];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line || 
			preg_match('/\n{2,}/', $item))
		{
			# Replace marker with the appropriate whitespace indentation
			$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
			$item = $this->runBlockGamut($this->outdent($item)."\n");
		}
		else {
			# Recursion for sub-lists:
			$item = $this->doLists($this->outdent($item));
			$item = preg_replace('/\n+$/', '', $item);
			$item = $this->runSpanGamut($item);
		}

		return "<li>" . $item . "</li>\n";
	}


	function doCodeBlocks($text) {
	#
	#	Process Markdown `<pre><code>` blocks.
	#
		$text = preg_replace_callback('{
				(?:\n\n|\A\n?)
				(	            # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->tab_width.'}  # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->tab_width.'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm',
			array(&$this, '_doCodeBlocks_callback'), $text);

		return $text;
	}
	function _doCodeBlocks_callback($matches) {
		$codeblock = $matches[1];

		$codeblock = $this->outdent($codeblock);
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

		# trim leading newlines and trailing newlines
		$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

		$codeblock = "<pre><code>$codeblock\n</code></pre>";
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
	}


	function makeCodeSpan($code) {
	#
	# Create a code span markup for $code. Called from handleSpanToken.
	#
		$code = htmlspecialchars(trim($code), ENT_NOQUOTES);
		return $this->hashPart("<code>$code</code>");
	}


	var $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?=\S|$)(?![\.,:;]\s)',
		'*' => '(?<=\S|^)(?<!\*)\*(?!\*)',
		'_' => '(?<=\S|^)(?<!_)_(?!_)',
		);
	var $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?=\S|$)(?![\.,:;]\s)',
		'**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)',
		'__' => '(?<=\S|^)(?<!_)__(?!_)',
		);
	var $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?=\S|$)(?![\.,:;]\s)',
		'***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)',
		'___' => '(?<=\S|^)(?<!_)___(?!_)',
		);
	var $em_strong_prepared_relist;
	
	function prepareItalicsAndBold() {
	#
	# Prepare regular expressions for searching emphasis tokens in any
	# context.
	#
		foreach ($this->em_relist as $em => $em_re) {
			foreach ($this->strong_relist as $strong => $strong_re) {
				# Construct list of allowed token expressions.
				$token_relist = array();
				if (isset($this->em_strong_relist["$em$strong"])) {
					$token_relist[] = $this->em_strong_relist["$em$strong"];
				}
				$token_relist[] = $em_re;
				$token_relist[] = $strong_re;
				
				# Construct master expression from list.
				$token_re = '{('. implode('|', $token_relist) .')}';
				$this->em_strong_prepared_relist["$em$strong"] = $token_re;
			}
		}
	}
	
	function doItalicsAndBold($text) {
		$token_stack = array('');
		$text_stack = array('');
		$em = '';
		$strong = '';
		$tree_char_em = false;
		
		while (1) {
			#
			# Get prepared regular expression for seraching emphasis tokens
			# in current context.
			#
			$token_re = $this->em_strong_prepared_relist["$em$strong"];
			
			#
			# Each loop iteration search for the next emphasis token. 
			# Each token is then passed to handleSpanToken.
			#
			$parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			$text_stack[0] .= $parts[0];
			$token =& $parts[1];
			$text =& $parts[2];
			
			if (empty($token)) {
				# Reached end of text span: empty stack without emitting.
				# any more emphasis.
				while ($token_stack[0]) {
					$text_stack[1] .= array_shift($token_stack);
					$text_stack[0] .= array_shift($text_stack);
				}
				break;
			}
			
			$token_len = strlen($token);
			if ($tree_char_em) {
				# Reached closing marker while inside a three-char emphasis.
				if ($token_len == 3) {
					# Three-char closing marker, close em and strong.
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong><em>$span</em></strong>";
					$text_stack[0] .= $this->hashPart($span);
					$em = '';
					$strong = '';
				} else {
					# Other closing marker: close one em or strong and
					# change current token state to match the other
					$token_stack[0] = str_repeat($token{0}, 3-$token_len);
					$tag = $token_len == 2 ? "strong" : "em";
					$span = $text_stack[0];
					$span = $this->runSpanGamut($span);
					$span = "<$tag>$span</$tag>";
					$text_stack[0] = $this->hashPart($span);
					$$tag = ''; # $$tag stands for $em or $strong
				}
				$tree_char_em = false;
			} else if ($token_len == 3) {
				if ($em) {
					# Reached closing marker for both em and strong.
					# Closing strong marker:
					for ($i = 0; $i < 2; ++$i) {
						$shifted_token = array_shift($token_stack);
						$tag = strlen($shifted_token) == 2 ? "strong" : "em";
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<$tag>$span</$tag>";
						$text_stack[0] .= $this->hashPart($span);
						$$tag = ''; # $$tag stands for $em or $strong
					}
				} else {
					# Reached opening three-char emphasis marker. Push on token 
					# stack; will be handled by the special condition above.
					$em = $token{0};
					$strong = "$em$em";
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$tree_char_em = true;
				}
			} else if ($token_len == 2) {
				if ($strong) {
					# Unwind any dangling emphasis marker:
					if (strlen($token_stack[0]) == 1) {
						$text_stack[1] .= array_shift($token_stack);
						$text_stack[0] .= array_shift($text_stack);
					}
					# Closing strong marker:
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong>$span</strong>";
					$text_stack[0] .= $this->hashPart($span);
					$strong = '';
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$strong = $token;
				}
			} else {
				# Here $token_len == 1
				if ($em) {
					if (strlen($token_stack[0]) == 1) {
						# Closing emphasis marker:
						array_shift($token_stack);
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<em>$span</em>";
						$text_stack[0] .= $this->hashPart($span);
						$em = '';
					} else {
						$text_stack[0] .= $token;
					}
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$em = $token;
				}
			}
		}
		return $text_stack[0];
	}


	function doBlockQuotes($text) {
		$text = preg_replace_callback('/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?			# ">" at the start of a line
					.+\n					# rest of the first line
				  (.+\n)*					# subsequent consecutive lines
				  \n*						# blanks
				)+
			  )
			/xm',
			array(&$this, '_doBlockQuotes_callback'), $text);

		return $text;
	}
	function _doBlockQuotes_callback($matches) {
		$bq = $matches[1];
		# trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = $this->runBlockGamut($bq);		# recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		# These leading spaces cause problem with <pre> content, 
		# so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', 
			array(&$this, '_doBlockQuotes_callback2'), $bq);

		return "\n". $this->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n";
	}
	function _doBlockQuotes_callback2($matches) {
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);
		return $pre;
	}


	function formParagraphs($text) {
	#
	#	Params:
	#		$text - string to process with html <p> tags
	#
		# Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);

		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		#
		# Wrap <p> tags and unhashify HTML blocks
		#
		foreach ($grafs as $key => $value) {
			if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
				# Is a paragraph.
				$value = $this->runSpanGamut($value);
				$value = preg_replace('/^([ ]*)/', "<p>", $value);
				$value .= "</p>";
				$grafs[$key] = $this->unhash($value);
			}
			else {
				# Is a block.
				# Modify elements of @grafs in-place...
				$graf = $value;
				$block = $this->html_hashes[$graf];
				$graf = $block;
//				if (preg_match('{
//					\A
//					(							# $1 = <div> tag
//					  <div  \s+
//					  [^>]*
//					  \b
//					  markdown\s*=\s*  ([\'"])	#	$2 = attr quote char
//					  1
//					  \2
//					  [^>]*
//					  >
//					)
//					(							# $3 = contents
//					.*
//					)
//					(</div>)					# $4 = closing tag
//					\z
//					}xs', $block, $matches))
//				{
//					list(, $div_open, , $div_content, $div_close) = $matches;
//
//					# We can't call Markdown(), because that resets the hash;
//					# that initialization code should be pulled into its own sub, though.
//					$div_content = $this->hashHTMLBlocks($div_content);
//					
//					# Run document gamut methods on the content.
//					foreach ($this->document_gamut as $method => $priority) {
//						$div_content = $this->$method($div_content);
//					}
//
//					$div_open = preg_replace(
//						'{\smarkdown\s*=\s*([\'"]).+?\1}', '', $div_open);
//
//					$graf = $div_open . "\n" . $div_content . "\n" . $div_close;
//				}
				$grafs[$key] = $graf;
			}
		}

		return implode("\n\n", $grafs);
	}


	function encodeAttribute($text) {
	#
	# Encode text for a double-quoted HTML attribute. This function
	# is *not* suitable for attributes enclosed in single quotes.
	#
		$text = $this->encodeAmpsAndAngles($text);
		$text = str_replace('"', '&quot;', $text);
		return $text;
	}
	
	
	function encodeAmpsAndAngles($text) {
	#
	# Smart processing for ampersands and angle brackets that need to 
	# be encoded. Valid character entities are left alone unless the
	# no-entities mode is set.
	#
		if ($this->no_entities) {
			$text = str_replace('&', '&amp;', $text);
		} else {
			# Ampersand-encoding based entirely on Nat Irons's Amputator
			# MT plugin: <http://bumppo.net/projects/amputator/>
			$text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', 
								'&amp;', $text);;
		}
		# Encode remaining <'s
		$text = str_replace('<', '&lt;', $text);

		return $text;
	}


	function doAutoLinks($text) {
		$text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i', 
			array(&$this, '_doAutoLinks_url_callback'), $text);

		# Email addresses: <address@domain.foo>
		$text = preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi',
			array(&$this, '_doAutoLinks_email_callback'), $text);

		return $text;
	}
	function _doAutoLinks_url_callback($matches) {
		$url = $this->encodeAttribute($matches[1]);
		$link = "<a href=\"$url\">$url</a>";
		return $this->hashPart($link);
	}
	function _doAutoLinks_email_callback($matches) {
		$address = $matches[1];
		$link = $this->encodeEmailAddress($address);
		return $this->hashPart($link);
	}


	function encodeEmailAddress($addr) {
	#
	#	Input: an email address, e.g. "foo@example.com"
	#
	#	Output: the email address as a mailto link, with each character
	#		of the address encoded as either a decimal or hex entity, in
	#		the hopes of foiling most address harvesting spam bots. E.g.:
	#
	#	  <p><a href="&#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x66;o&#111;
	#        &#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;&#101;&#46;&#x63;&#111;
	#        &#x6d;">&#x66;o&#111;&#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;
	#        &#101;&#46;&#x63;&#111;&#x6d;</a></p>
	#
	#	Based by a filter by Matthew Wickline, posted to BBEdit-Talk.
	#   With some optimizations by Milian Wolff.
	#
		$addr = "mailto:" . $addr;
		$chars = preg_split('/(?<!^)(?!$)/', $addr);
		$seed = (int)abs(crc32($addr) / strlen($addr)); # Deterministic seed.
		
		foreach ($chars as $key => $char) {
			$ord = ord($char);
			# Ignore non-ascii chars.
			if ($ord < 128) {
				$r = ($seed * (1 + $key)) % 100; # Pseudo-random function.
				# roughly 10% raw, 45% hex, 45% dec
				# '@' *must* be encoded. I insist.
				if ($r > 90 && $char != '@') /* do nothing */;
				else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';';
				else              $chars[$key] = '&#'.$ord.';';
			}
		}
		
		$addr = implode('', $chars);
		$text = implode('', array_slice($chars, 7)); # text without `mailto:`
		$addr = "<a href=\"$addr\">$text</a>";

		return $addr;
	}


	function parseSpan($str) {
	#
	# Take the string $str and parse it into tokens, hashing embeded HTML,
	# escaped characters and handling code spans.
	#
		$output = '';
		
		$span_re = '{
				(
					\\\\'.$this->escape_chars_re.'
				|
					(?<![`\\\\])
					`+						# code span marker
			'.( $this->no_markup ? '' : '
				|
					<!--    .*?     -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[/!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
			').'
				)
				}xs';

		while (1) {
			#
			# Each loop iteration seach for either the next tag, the next 
			# openning code span marker, or the next escaped character. 
			# Each token is then passed to handleSpanToken.
			#
			$parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			# Create token from text preceding tag.
			if ($parts[0] != "") {
				$output .= $parts[0];
			}
			
			# Check if we reach the end.
			if (isset($parts[1])) {
				$output .= $this->handleSpanToken($parts[1], $parts[2]);
				$str = $parts[2];
			}
			else {
				break;
			}
		}
		
		return $output;
	}
	
	
	function handleSpanToken($token, &$str) {
	#
	# Handle $token provided by parseSpan by determining its nature and 
	# returning the corresponding value that should replace it.
	#
		switch ($token{0}) {
			case "\\":
				return $this->hashPart("&#". ord($token{1}). ";");
			case "`":
				# Search for end marker in remaining text.
				if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm', 
					$str, $matches))
				{
					$str = $matches[2];
					$codespan = $this->makeCodeSpan($matches[1]);
					return $this->hashPart($codespan);
				}
				return $token; // return as text since no ending marker found.
			default:
				return $this->hashPart($token);
		}
	}


	function outdent($text) {
	#
	# Remove one level of line-leading tabs or spaces
	#
		return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text);
	}


	# String length function for detab. `_initDetab` will create a function to 
	# hanlde UTF-8 if the default function does not exist.
	var $utf8_strlen = 'mb_strlen';
	
	function detab($text) {
	#
	# Replace tabs with the appropriate amount of space.
	#
		# For each line we separate the line in blocks delemited by
		# tab characters. Then we reconstruct every line by adding the 
		# appropriate number of space between each blocks.
		
		$text = preg_replace_callback('/^.*\t.*$/m',
			array(&$this, '_detab_callback'), $text);

		return $text;
	}
	function _detab_callback($matches) {
		$line = $matches[0];
		$strlen = $this->utf8_strlen; # strlen function for UTF-8.
		
		# Split in blocks.
		$blocks = explode("\t", $line);
		# Add each blocks to the line.
		$line = $blocks[0];
		unset($blocks[0]); # Do not add first block twice.
		foreach ($blocks as $block) {
			# Calculate amount of space, insert spaces, insert block.
			$amount = $this->tab_width - 
				$strlen($line, 'UTF-8') % $this->tab_width;
			$line .= str_repeat(" ", $amount) . $block;
		}
		return $line;
	}
	function _initDetab() {
	#
	# Check for the availability of the function in the `utf8_strlen` property
	# (initially `mb_strlen`). If the function is not available, create a 
	# function that will loosely count the number of UTF-8 characters with a
	# regular expression.
	#
		if (function_exists($this->utf8_strlen)) return;
		$this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", 
			$text, $m);');
	}


	function unhash($text) {
	#
	# Swap back in all the tags hashed by _HashHTMLBlocks.
	#
		return preg_replace_callback('/(.)\x1A[0-9]+\1/', 
			array(&$this, '_unhash_callback'), $text);
	}
	function _unhash_callback($matches) {
		return $this->html_hashes[$matches[0]];
	}

}

/*

PHP Markdown
============

Description
-----------

This is a PHP translation of the original Markdown formatter written in
Perl by John Gruber.

Markdown is a text-to-HTML filter; it translates an easy-to-read /
easy-to-write structured text format into HTML. Markdown's text format
is most similar to that of plain text email, and supports features such
as headers, *emphasis*, code blocks, blockquotes, and links.

Markdown's syntax is designed not as a generic markup language, but
specifically to serve as a front-end to (X)HTML. You can use span-level
HTML tags anywhere in a Markdown document, and you can use block level
HTML tags (like <div> and <table> as well).

For more information about Markdown's syntax, see:

<http://daringfireball.net/projects/markdown/>


Bugs
----

To file bug reports please send email to:

<michel.fortin@michelf.com>

Please include with your report: (1) the example input; (2) the output you
expected; (3) the output Markdown actually produced.


Version History
--------------- 

See the readme file for detailed release notes for this version.


Copyright and License
---------------------

PHP Markdown
Copyright (c) 2004-2009 Michel Fortin  
<http://michelf.com/>  
All rights reserved.

Based on Markdown
Copyright (c) 2003-2006 John Gruber   
<http://daringfireball.net/>   
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

*	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.

*	Redistributions in binary form must reproduce the above copyright
	notice, this list of conditions and the following disclaimer in the
	documentation and/or other materials provided with the distribution.

*	Neither the name "Markdown" nor the names of its contributors may
	be used to endorse or promote products derived from this software
	without specific prior written permission.

This software is provided by the copyright holders and contributors "as
is" and any express or implied warranties, including, but not limited
to, the implied warranties of merchantability and fitness for a
particular purpose are disclaimed. In no event shall the copyright owner
or contributors be liable for any direct, indirect, incidental, special,
exemplary, or consequential damages (including, but not limited to,
procurement of substitute goods or services; loss of use, data, or
profits; or business interruption) however caused and on any theory of
liability, whether in contract, strict liability, or tort (including
negligence or otherwise) arising in any way out of the use of this
software, even if advised of the possibility of such damage.

*/





/* ========================================================= markdownify_extra.php ============== */

/**
 * Class to convert HTML to Markdown with PHP Markdown Extra syntax support.
 *
 * @version 1.0.0 alpha
 * @author Milian Wolff (<mail@milianw.de>, <http://milianw.de>)
 * @license LGPL, see LICENSE_LGPL.txt and the summary below
 * @copyright (C) 2007  Milian Wolff
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * standard Markdownify class
 */
/**
 * Markdownify converts HTML Markup to [Markdown][1] (by [John Gruber][2]. It
 * also supports [Markdown Extra][3] by [Michel Fortin][4] via Markdownify_Extra.
 *
 * It all started as `html2text.php` - a port of [Aaron Swartz'][5] [`html2text.py`][6] - but
 * got a long way since. This is far more than a mere port now!
 * Starting with version 2.0.0 this is a complete rewrite and cannot be
 * compared to Aaron Swatz' `html2text.py` anylonger. I'm now using a HTML parser
 * (see `parsehtml.php` which I also wrote) which makes most of the evil
 * RegEx magic go away and additionally it gives a much cleaner class
 * structure. Also notably is the fact that I now try to prevent regressions by
 * utilizing testcases of Michel Fortin's [MDTest][7].
 *
 * [1]: http://daringfireball.com/projects/markdown
 * [2]: http://daringfireball.com/
 * [3]: http://www.michelf.com/projects/php-markdown/extra/
 * [4]: http://www.michelf.com/
 * [5]: http://www.aaronsw.com/
 * [6]: http://www.aaronsw.com/2002/html2text/
 * [7]: http://article.gmane.org/gmane.text.markdown.general/2540
 *
 * @version 2.0.0 alpha
 * @author Milian Wolff (<mail@milianw.de>, <http://milianw.de>)
 * @license LGPL, see LICENSE_LGPL.txt and the summary below
 * @copyright (C) 2007  Milian Wolff
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * HTML Parser, see http://sf.net/projects/parseHTML
 */
/**
 * parseHTML is a HTML parser which works with PHP 4 and above.
 * It tries to handle invalid HTML to some degree.
 *
 * @version 1.0 beta
 * @author Milian Wolff (mail@milianw.de, http://milianw.de)
 * @license LGPL, see LICENSE_LGPL.txt and the summary below
 * @copyright (C) 2007  Milian Wolff
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */
class parseHTML {
  /**
   * tags which are always empty (<br /> etc.)
   *
   * @var array<string>
   */
  var $emptyTags = array(
    'br',
    'hr',
    'input',
    'img',
    'area',
    'link',
    'meta',
    'param',
  );
  /**
   * tags with preformatted text
   * whitespaces wont be touched in them
   *
   * @var array<string>
   */
  var $preformattedTags = array(
    'script',
    'style',
    'pre',
    'code',
  );
  /**
   * supress HTML tags inside preformatted tags (see above)
   *
   * @var bool
   */
  var $noTagsInCode = false;
  /**
   * html to be parsed
   *
   * @var string
   */
  var $html = '';
  /**
   * node type:
   *
   * - tag (see isStartTag)
   * - text (includes cdata)
   * - comment
   * - doctype
   * - pi (processing instruction)
   *
   * @var string
   */
  var $nodeType = '';
  /**
   * current node content, i.e. either a
   * simple string (text node), or something like
   * <tag attrib="value"...>
   *
   * @var string
   */
  var $node = '';
  /**
   * wether current node is an opening tag (<a>) or not (</a>)
   * set to NULL if current node is not a tag
   * NOTE: empty tags (<br />) set this to true as well!
   *
   * @var bool | null
   */
  var $isStartTag = null;
  /**
   * wether current node is an empty tag (<br />) or not (<a></a>)
   *
   * @var bool | null
   */
  var $isEmptyTag = null;
  /**
   * tag name
   *
   * @var string | null
   */
  var $tagName = '';
  /**
   * attributes of current tag
   *
   * @var array (attribName=>value) | null
   */
  var $tagAttributes = null;
  /**
   * wether the current tag is a block element
   *
   * @var bool | null
   */
  var $isBlockElement = null;

  /**
   * keep whitespace
   *
   * @var int
   */
  var $keepWhitespace = 0;
  /**
   * list of open tags
   * count this to get current depth
   *
   * @var array
   */
  var $openTags = array();
  /**
   * list of block elements
   *
   * @var array
   * TODO: what shall we do with <del> and <ins> ?!
   */
  var $blockElements = array (
    # tag name => <bool> is block
    # block elements
    'address' => true,
    'blockquote' => true,
    'center' => true,
    'del' => true,
    'dir' => true,
    'div' => true,
    'dl' => true,
    'fieldset' => true,
    'form' => true,
    'h1' => true,
    'h2' => true,
    'h3' => true,
    'h4' => true,
    'h5' => true,
    'h6' => true,
    'hr' => true,
    'ins' => true,
    'isindex' => true,
    'menu' => true,
    'noframes' => true,
    'noscript' => true,
    'ol' => true,
    'p' => true,
    'pre' => true,
    'table' => true,
    'ul' => true,
    # set table elements and list items to block as well
    'thead' => true,
    'tbody' => true,
    'tfoot' => true,
    'td' => true,
    'tr' => true,
    'th' => true,
    'li' => true,
    'dd' => true,
    'dt' => true,
    # header items and html / body as well
    'html' => true,
    'body' => true,
    'head' => true,
    'meta' => true,
    'link' => true,
    'style' => true,
    'title' => true,
    # unfancy media tags, when indented should be rendered as block
    'map' => true,
    'object' => true,
    'param' => true,
    'embed' => true,
    'area' => true,
    # inline elements
    'a' => false,
    'abbr' => false,
    'acronym' => false,
    'applet' => false,
    'b' => false,
    'basefont' => false,
    'bdo' => false,
    'big' => false,
    'br' => false,
    'button' => false,
    'cite' => false,
    'code' => false,
    'del' => false,
    'dfn' => false,
    'em' => false,
    'font' => false,
    'i' => false,
    'img' => false,
    'ins' => false,
    'input' => false,
    'iframe' => false,
    'kbd' => false,
    'label' => false,
    'q' => false,
    'samp' => false,
    'script' => false,
    'select' => false,
    'small' => false,
    'span' => false,
    'strong' => false,
    'sub' => false,
    'sup' => false,
    'textarea' => false,
    'tt' => false,
    'var' => false,
  );
  /**
   * get next node, set $this->html prior!
   *
   * @param void
   * @return bool
   */
  function nextNode() {
    if (empty($this->html)) {
      # we are done with parsing the html string
      return false;
    }
    static $skipWhitespace = true;
    if ($this->isStartTag && !$this->isEmptyTag) {
      array_push($this->openTags, $this->tagName);
      if (in_array($this->tagName, $this->preformattedTags)) {
        # dont truncate whitespaces for <code> or <pre> contents
        $this->keepWhitespace++;
      }
    }

    if ($this->html[0] == '<') {
      $token = substr($this->html, 0, 9);
      if (substr($token, 0, 2) == '<?') {
        # xml prolog or other pi's
        /** TODO **/
        #trigger_error('this might need some work', E_USER_NOTICE);
        $pos = strpos($this->html, '>');
        $this->setNode('pi', $pos + 1);
        return true;
      }
      if (substr($token, 0, 4) == '<!--') {
        # comment
        $pos = strpos($this->html, '-->');
        if ($pos === false) {
          # could not find a closing -->, use next gt instead
          # this is firefox' behaviour
          $pos = strpos($this->html, '>') + 1;
        } else {
          $pos += 3;
        }
        $this->setNode('comment', $pos);

        $skipWhitespace = true;
        return true;
      }
      if ($token == '<!DOCTYPE') {
        # doctype
        $this->setNode('doctype', strpos($this->html, '>')+1);

        $skipWhitespace = true;
        return true;
      }
      if ($token == '<![CDATA[') {
        # cdata, use text node

        # remove leading <![CDATA[
        $this->html = substr($this->html, 9);

        $this->setNode('text', strpos($this->html, ']]>')+3);

        # remove trailing ]]> and trim
        $this->node = substr($this->node, 0, -3);
        $this->handleWhitespaces();

        $skipWhitespace = true;
        return true;
      }
      if ($this->parseTag()) {
        # seems to be a tag
        # handle whitespaces
        if ($this->isBlockElement) {
          $skipWhitespace = true;
        } else {
          $skipWhitespace = false;
        }
        return true;
      }
    }
    if ($this->keepWhitespace) {
      $skipWhitespace = false;
    }
    # when we get here it seems to be a text node
    $pos = strpos($this->html, '<');
    if ($pos === false) {
      $pos = strlen($this->html);
    }
    $this->setNode('text', $pos);
    $this->handleWhitespaces();
    if ($skipWhitespace && $this->node == ' ') {
      return $this->nextNode();
    }
    $skipWhitespace = false;
    return true;
  }
  /**
   * parse tag, set tag name and attributes, see if it's a closing tag and so forth...
   *
   * @param void
   * @return bool
   */
  function parseTag() {
    static $a_ord, $z_ord, $special_ords;
    if (!isset($a_ord)) {
      $a_ord = ord('a');
      $z_ord = ord('z');
      $special_ords = array(
        ord(':'), // for xml:lang
        ord('-'), // for http-equiv
      );
    }

    $tagName = '';

    $pos = 1;
    $isStartTag = $this->html[$pos] != '/';
    if (!$isStartTag) {
      $pos++;
    }
    # get tagName
    while (isset($this->html[$pos])) {
      $pos_ord = ord(strtolower($this->html[$pos]));
      if (($pos_ord >= $a_ord && $pos_ord <= $z_ord) || (!empty($tagName) && is_numeric($this->html[$pos]))) {
        $tagName .= $this->html[$pos];
        $pos++;
      } else {
        $pos--;
        break;
      }
    }

    $tagName = strtolower($tagName);
    if (empty($tagName) || !isset($this->blockElements[$tagName])) {
      # something went wrong => invalid tag
      $this->invalidTag();
      return false;
    }
    if ($this->noTagsInCode && end($this->openTags) == 'code' && !($tagName == 'code' && !$isStartTag)) {
      # we supress all HTML tags inside code tags
      $this->invalidTag();
      return false;
    }

    # get tag attributes
    /** TODO: in html 4 attributes do not need to be quoted **/
    $isEmptyTag = false;
    $attributes = array();
    $currAttrib = '';
    while (isset($this->html[$pos+1])) {
      $pos++;
      # close tag
      if ($this->html[$pos] == '>' || $this->html[$pos].$this->html[$pos+1] == '/>') {
        if ($this->html[$pos] == '/') {
          $isEmptyTag = true;
          $pos++;
        }
        break;
      }

      $pos_ord = ord(strtolower($this->html[$pos]));
      if ( ($pos_ord >= $a_ord && $pos_ord <= $z_ord) || in_array($pos_ord, $special_ords)) {
        # attribute name
        $currAttrib .= $this->html[$pos];
      } elseif (in_array($this->html[$pos], array(' ', "\t", "\n"))) {
        # drop whitespace
      } elseif (in_array($this->html[$pos].$this->html[$pos+1], array('="', "='"))) {
        # get attribute value
        $pos++;
        $await = $this->html[$pos]; # single or double quote
        $pos++;
        $value = '';
        while (isset($this->html[$pos]) && $this->html[$pos] != $await) {
          $value .= $this->html[$pos];
          $pos++;
        }
        $attributes[$currAttrib] = $value;
        $currAttrib = '';
      } else {
        $this->invalidTag();
        return false;
      }
    }
    if ($this->html[$pos] != '>') {
      $this->invalidTag();
      return false;
    }

    if (!empty($currAttrib)) {
      # html 4 allows something like <option selected> instead of <option selected="selected">
      $attributes[$currAttrib] = $currAttrib;
    }
    if (!$isStartTag) {
      if (!empty($attributes) || $tagName != end($this->openTags)) {
        # end tags must not contain any attributes
        # or maybe we did not expect a different tag to be closed
        $this->invalidTag();
        return false;
      }
      array_pop($this->openTags);
      if (in_array($tagName, $this->preformattedTags)) {
        $this->keepWhitespace--;
      }
    }
    $pos++;
    $this->node = substr($this->html, 0, $pos);
    $this->html = substr($this->html, $pos);
    $this->tagName = $tagName;
    $this->tagAttributes = $attributes;
    $this->isStartTag = $isStartTag;
    $this->isEmptyTag = $isEmptyTag || in_array($tagName, $this->emptyTags);
    if ($this->isEmptyTag) {
      # might be not well formed
      $this->node = preg_replace('# */? *>$#', ' />', $this->node);
    }
    $this->nodeType = 'tag';
    $this->isBlockElement = $this->blockElements[$tagName];
    return true;
  }
  /**
   * handle invalid tags
   *
   * @param void
   * @return void
   */
  function invalidTag() {
    $this->html = substr_replace($this->html, '&lt;', 0, 1);
  }
  /**
   * update all vars and make $this->html shorter
   *
   * @param string $type see description for $this->nodeType
   * @param int $pos to which position shall we cut?
   * @return void
   */
  function setNode($type, $pos) {
    if ($this->nodeType == 'tag') {
      # set tag specific vars to null
      # $type == tag should not be called here
      # see this::parseTag() for more
      $this->tagName = null;
      $this->tagAttributes = null;
      $this->isStartTag = null;
      $this->isEmptyTag = null;
      $this->isBlockElement = null;

    }
    $this->nodeType = $type;
    $this->node = substr($this->html, 0, $pos);
    $this->html = substr($this->html, $pos);
  }
  /**
   * check if $this->html begins with $str
   *
   * @param string $str
   * @return bool
   */
  function match($str) {
    return substr($this->html, 0, strlen($str)) == $str;
  }
  /**
   * truncate whitespaces
   *
   * @param void
   * @return void
   */
  function handleWhitespaces() {
    if ($this->keepWhitespace) {
      # <pre> or <code> before...
      return;
    }
    # truncate multiple whitespaces to a single one
    $this->node = preg_replace('#\s+#s', ' ', $this->node);
  }
  /**
   * normalize self::node
   *
   * @param void
   * @return void
   */
  function normalizeNode() {
    $this->node = '<';
    if (!$this->isStartTag) {
      $this->node .= '/'.$this->tagName.'>';
      return;
    }
    $this->node .= $this->tagName;
    foreach ($this->tagAttributes as $name => $value) {
      $this->node .= ' '.$name.'="'.str_replace('"', '&quot;', $value).'"';
    }
    if ($this->isEmptyTag) {
      $this->node .= ' /';
    }
    $this->node .= '>';
  }
}

/**
 * indent a HTML string properly
 *
 * @param string $html
 * @param string $indent optional
 * @return string
 */
function indentHTML($html, $indent = "  ", $noTagsInCode = false) {
  $parser = new parseHTML;
  $parser->noTagsInCode = $noTagsInCode;
  $parser->html = $html;
  $html = '';
  $last = true; # last tag was block elem
  $indent_a = array();
  while($parser->nextNode()) {
    if ($parser->nodeType == 'tag') {
      $parser->normalizeNode();
    }
    if ($parser->nodeType == 'tag' && $parser->isBlockElement) {
      $isPreOrCode = in_array($parser->tagName, array('code', 'pre'));
      if (!$parser->keepWhitespace && !$last && !$isPreOrCode) {
        $html = rtrim($html)."\n";
      }
      if ($parser->isStartTag) {
        $html .= implode($indent_a);
        if (!$parser->isEmptyTag) {
          array_push($indent_a, $indent);
        }
      } else {
        array_pop($indent_a);
        if (!$isPreOrCode) {
          $html .= implode($indent_a);
        }
      }
      $html .= $parser->node;
      if (!$parser->keepWhitespace && !($isPreOrCode && $parser->isStartTag)) {
        $html .= "\n";
      }
      $last = true;
    } else {
      if ($parser->nodeType == 'tag' && $parser->tagName == 'br') {
        $html .= $parser->node."\n";
        $last = true;
        continue;
      } elseif ($last && !$parser->keepWhitespace) {
        $html .= implode($indent_a);
        $parser->node = ltrim($parser->node);
      }
      $html .= $parser->node;

      if (in_array($parser->nodeType, array('comment', 'pi', 'doctype'))) {
        $html .= "\n";
      } else {
        $last = false;
      }
    }
  }
  return $html;
}
/*
# testcase / example
error_reporting(E_ALL);

$html = '<p>Simple block on one line:</p>

<div>foo</div>

<p>And nested without indentation:</p>

<div>
<div>
<div>
foo
</div>
<div style=">"/>
</div>
<div>bar</div>
</div>

<p>And with attributes:</p>

<div>
    <div id="foo">
    </div>
</div>

<p>This was broken in 1.0.2b7:</p>

<div class="inlinepage">
<div class="toggleableend">
foo
</div>
</div>';
#$html = '<a href="asdfasdf"       title=\'asdf\' foo="bar">asdf</a>';
echo indentHTML($html);
die();
*/


/**
 * default configuration
 */
define('MDFY_LINKS_EACH_PARAGRAPH', false);
define('MDFY_BODYWIDTH', false);
define('MDFY_KEEPHTML', true);

/**
 * HTML to Markdown converter class
 */
class Markdownify {
  /**
   * html parser object
   *
   * @var parseHTML
   */
  var $parser;
  /**
   * markdown output
   *
   * @var string
   */
  var $output;
  /**
   * stack with tags which where not converted to html
   *
   * @var array<string>
   */
  var $notConverted = array();
  /**
   * skip conversion to markdown
   *
   * @var bool
   */
  var $skipConversion = false;
  /* options */
  /**
   * keep html tags which cannot be converted to markdown
   *
   * @var bool
   */
  var $keepHTML = false;
  /**
   * wrap output, set to 0 to skip wrapping
   *
   * @var int
   */
  var $bodyWidth = 0;
  /**
   * minimum body width
   *
   * @var int
   */
  var $minBodyWidth = 25;
  /**
   * display links after each paragraph
   *
   * @var bool
   */
  var $linksAfterEachParagraph = false;
  /**
   * constructor, set options, setup parser
   *
   * @param bool $linksAfterEachParagraph wether or not to flush stacked links after each paragraph
   *             defaults to false
   * @param int $bodyWidth wether or not to wrap the output to the given width
   *             defaults to false
   * @param bool $keepHTML wether to keep non markdownable HTML or to discard it
   *             defaults to true (HTML will be kept)
   * @return void
   */
  function Markdownify($linksAfterEachParagraph = MDFY_LINKS_EACH_PARAGRAPH, $bodyWidth = MDFY_BODYWIDTH, $keepHTML = MDFY_KEEPHTML) {
    $this->linksAfterEachParagraph = $linksAfterEachParagraph;
    $this->keepHTML = $keepHTML;

    if ($bodyWidth > $this->minBodyWidth) {
      $this->bodyWidth = intval($bodyWidth);
    } else {
      $this->bodyWidth = false;
    }

    $this->parser = new parseHTML;
    $this->parser->noTagsInCode = true;

    # we don't have to do this every time
    $search = array();
    $replace = array();
    foreach ($this->escapeInText as $s => $r) {
      array_push($search, '#(?<!\\\)'.$s.'#U');
      array_push($replace, $r);
    }
    $this->escapeInText = array(
      'search' => $search,
      'replace' => $replace
    );
  }
  /**
   * parse a HTML string
   *
   * @param string $html
   * @return string markdown formatted
   */
  function parseString($html) {
    $this->parser->html = $html;
    $this->parse();
    return $this->output;
  }
  /**
   * tags with elements which can be handled by markdown
   *
   * @var array<string>
   */
  var $isMarkdownable = array(
    'p' => array(),
    'ul' => array(),
    'ol' => array(),
    'li' => array(),
    'br' => array(),
    'blockquote' => array(),
    'code' => array(),
    'pre' => array(),
    'a' => array(
      'href' => 'required',
      'title' => 'optional',
    ),
    'strong' => array(),
    'b' => array(),
    'em' => array(),
    'i' => array(),
    'img' => array(
      'src' => 'required',
      'alt' => 'optional',
      'title' => 'optional',
    ),
    'h1' => array(),
    'h2' => array(),
    'h3' => array(),
    'h4' => array(),
    'h5' => array(),
    'h6' => array(),
    'hr' => array(),
  );
  /**
   * html tags to be ignored (contents will be parsed)
   *
   * @var array<string>
   */
  var $ignore = array(
    'html',
    'body',
  );
  /**
   * html tags to be dropped (contents will not be parsed!)
   *
   * @var array<string>
   */
  var $drop = array(
    'script',
    'head',
    'style',
    'form',
    'area',
    'object',
    'param',
    'iframe',
  );
  /**
   * Markdown indents which could be wrapped
   * @note: use strings in regex format
   *
   * @var array<string>
   */
  var $wrappableIndents = array(
    '\*   ', # ul
    '\d.  ', # ol
    '\d\d. ', # ol
    '> ', # blockquote
    '', # p
  );
  /**
   * list of chars which have to be escaped in normal text
   * @note: use strings in regex format
   *
   * @var array
   *
   * TODO: what's with block chars / sequences at the beginning of a block?
   */
  var $escapeInText = array(
    '([-*_])([ ]{0,2}\1){2,}' => '\\\\$0|', # hr
    '\*\*([^*\s]+)\*\*' => '\*\*$1\*\*', # strong
    '\*([^*\s]+)\*' => '\*$1\*', # em
    '__(?! |_)(.+)(?!<_| )__' => '\_\_$1\_\_', # em
    '_(?! |_)(.+)(?!<_| )_' => '\_$1\_', # em
    '`(.+)`' => '\`$1\`', # code
    '\[(.+)\](\s*\()' => '\[$1\]$2', # links: [text] (url) => [text\] (url)
    '\[(.+)\](\s*)\[(.*)\]' => '\[$1\]$2\[$3\]', # links: [text][id] => [text\][id\]
  );
  /**
   * wether last processed node was a block tag or not
   *
   * @var bool
   */
  var $lastWasBlockTag = false;
  /**
   * name of last closed tag
   *
   * @var string
   */
  var $lastClosedTag = '';
  /**
   * iterate through the nodes and decide what we
   * shall do with the current node
   *
   * @param void
   * @return void
   */
  function parse() {
    $this->output = '';
    # drop tags
    $this->parser->html = preg_replace('#<('.implode('|', $this->drop).')[^>]*>.*</\\1>#sU', '', $this->parser->html);
    while ($this->parser->nextNode()) {
      switch ($this->parser->nodeType) {
        case 'doctype':
          break;
        case 'pi':
        case 'comment':
          if ($this->keepHTML) {
            $this->flushLinebreaks();
            $this->out($this->parser->node);
            $this->setLineBreaks(2);
          }
          # else drop
          break;
        case 'text':
          $this->handleText();
          break;
        case 'tag':
          if (in_array($this->parser->tagName, $this->ignore)) {
            break;
          }
          if ($this->parser->isStartTag) {
            $this->flushLinebreaks();
          }
          if ($this->skipConversion) {
            $this->isMarkdownable(); # update notConverted
            $this->handleTagToText();
            continue;
          }
          if (!$this->parser->keepWhitespace && $this->parser->isBlockElement && $this->parser->isStartTag) {
            $this->parser->html = ltrim($this->parser->html);
          }
          if ($this->isMarkdownable()) {
            if ($this->parser->isBlockElement && $this->parser->isStartTag && !$this->lastWasBlockTag && !empty($this->output)) {
              if (!empty($this->buffer)) {
                $str =& $this->buffer[count($this->buffer) -1];
              } else {
                $str =& $this->output;
              }
              if (substr($str, -strlen($this->indent)-1) != "\n".$this->indent) {
                $str .= "\n".$this->indent;
              }
            }
            $func = 'handleTag_'.$this->parser->tagName;
            $this->$func();
            if ($this->linksAfterEachParagraph && $this->parser->isBlockElement && !$this->parser->isStartTag && empty($this->parser->openTags)) {
              $this->flushStacked();
            }
            if (!$this->parser->isStartTag) {
              $this->lastClosedTag = $this->parser->tagName;
            }
          } else {
            $this->handleTagToText();
            $this->lastClosedTag = '';
          }
          break;
        default:
          trigger_error('invalid node type', E_USER_ERROR);
          break;
      }
      $this->lastWasBlockTag = $this->parser->nodeType == 'tag' && $this->parser->isStartTag && $this->parser->isBlockElement;
    }
    if (!empty($this->buffer)) {
      trigger_error('buffer was not flushed, this is a bug. please report!', E_USER_WARNING);
      while (!empty($this->buffer)) {
        $this->out($this->unbuffer());
      }
    }
    ### cleanup
    $this->output = rtrim(str_replace('&amp;', '&', str_replace('&lt;', '<', str_replace('&gt;', '>', $this->output))));
    # end parsing, flush stacked tags
    $this->flushStacked();
    $this->stack = array();
  }
  /**
   * check if current tag can be converted to Markdown
   *
   * @param void
   * @return bool
   */
  function isMarkdownable() {
    if (!isset($this->isMarkdownable[$this->parser->tagName])) {
      # simply not markdownable
      return false;
    }
    if ($this->parser->isStartTag) {
      $return = true;
      if ($this->keepHTML) {
        $diff = array_diff(array_keys($this->parser->tagAttributes), array_keys($this->isMarkdownable[$this->parser->tagName]));
        if (!empty($diff)) {
          # non markdownable attributes given
          $return = false;
        }
      }
      if ($return) {
        foreach ($this->isMarkdownable[$this->parser->tagName] as $attr => $type) {
          if ($type == 'required' && !isset($this->parser->tagAttributes[$attr])) {
            # required markdown attribute not given
            $return = false;
            break;
          }
        }
      }
      if (!$return) {
        array_push($this->notConverted, $this->parser->tagName.'::'.implode('/', $this->parser->openTags));
      }
      return $return;
    } else {
      if (!empty($this->notConverted) && end($this->notConverted) === $this->parser->tagName.'::'.implode('/', $this->parser->openTags)) {
        array_pop($this->notConverted);
        return false;
      }
      return true;
    }
  }
  /**
   * output all stacked tags
   *
   * @param void
   * @return void
   */
  function flushStacked() {
    # links
    foreach ($this->stack as $tag => $a) {
      if (!empty($a)) {
        call_user_func(array(&$this, 'flushStacked_'.$tag));
      }
    }
  }
  /**
   * output link references (e.g. [1]: http://example.com "title");
   *
   * @param void
   * @return void
   */
  function flushStacked_a() {
    $out = false;
    foreach ($this->stack['a'] as $k => $tag) {
      if (!isset($tag['unstacked'])) {
        if (!$out) {
          $out = true;
          $this->out("\n\n", true);
        } else {
          $this->out("\n", true);
        }
        $this->out(' ['.$tag['linkID'].']: '.$tag['href'].(isset($tag['title']) ? ' "'.$tag['title'].'"' : ''), true);
        $tag['unstacked'] = true;
        $this->stack['a'][$k] = $tag;
      }
    }
  }
  /**
   * flush enqued linebreaks
   *
   * @param void
   * @return void
   */
  function flushLinebreaks() {
    if ($this->lineBreaks && !empty($this->output)) {
      $this->out(str_repeat("\n".$this->indent, $this->lineBreaks), true);
    }
    $this->lineBreaks = 0;
  }
  /**
   * handle non Markdownable tags
   *
   * @param void
   * @return void
   */
  function handleTagToText() {
    if (!$this->keepHTML) {
      if (!$this->parser->isStartTag && $this->parser->isBlockElement) {
        $this->setLineBreaks(2);
      }
    } else {
      # dont convert to markdown inside this tag
      /** TODO: markdown extra **/
      if (!$this->parser->isEmptyTag) {
        if ($this->parser->isStartTag) {
          if (!$this->skipConversion) {
            $this->skipConversion = $this->parser->tagName.'::'.implode('/', $this->parser->openTags);
          }
        } else {
          if ($this->skipConversion == $this->parser->tagName.'::'.implode('/', $this->parser->openTags)) {
            $this->skipConversion = false;
          }
        }
      }

      if ($this->parser->isBlockElement) {
        if ($this->parser->isStartTag) {
          if (in_array($this->parent(), array('ins', 'del'))) {
            # looks like ins or del are block elements now
            $this->out("\n", true);
            $this->indent('  ');
          }
          if ($this->parser->tagName != 'pre') {
            $this->out($this->parser->node."\n".$this->indent);
            if (!$this->parser->isEmptyTag) {
              $this->indent('  ');
            } else {
              $this->setLineBreaks(1);
            }
            $this->parser->html = ltrim($this->parser->html);
          } else {
            # don't indent inside <pre> tags
            $this->out($this->parser->node);
            static $indent;
            $indent =  $this->indent;
            $this->indent = '';
          }
        } else {
          if (!$this->parser->keepWhitespace) {
            $this->output = rtrim($this->output);
          }
          if ($this->parser->tagName != 'pre') {
            $this->indent('  ');
            $this->out("\n".$this->indent.$this->parser->node);
          } else {
            # reset indentation
            $this->out($this->parser->node);
            static $indent;
            $this->indent = $indent;
          }

          if (in_array($this->parent(), array('ins', 'del'))) {
            # ins or del was block element
            $this->out("\n");
            $this->indent('  ');
          }
          if ($this->parser->tagName == 'li') {
            $this->setLineBreaks(1);
          } else {
            $this->setLineBreaks(2);
          }
        }
      } else {
        $this->out($this->parser->node);
      }
      if (in_array($this->parser->tagName, array('code', 'pre'))) {
        if ($this->parser->isStartTag) {
          $this->buffer();
        } else {
          # add stuff so cleanup just reverses this
          $this->out(str_replace('&lt;', '&amp;lt;', str_replace('&gt;', '&amp;gt;', $this->unbuffer())));
        }
      }
    }
  }
  /**
   * handle plain text
   *
   * @param void
   * @return void
   */
  function handleText() {
    if ($this->hasParent('pre') && strpos($this->parser->node, "\n") !== false) {
      $this->parser->node = str_replace("\n", "\n".$this->indent, $this->parser->node);
    }
    if (!$this->hasParent('code') && !$this->hasParent('pre')) {
      # entity decode
      $this->parser->node = $this->decode($this->parser->node);
      if (!$this->skipConversion) {
        # escape some chars in normal Text
        $this->parser->node = preg_replace($this->escapeInText['search'], $this->escapeInText['replace'], $this->parser->node);
      }
    } else {
      $this->parser->node = str_replace(array('&quot;', '&apos'), array('"', '\''), $this->parser->node);
    }
    $this->out($this->parser->node);
    $this->lastClosedTag = '';
  }
  /**
   * handle <em> and <i> tags
   *
   * @param void
   * @return void
   */
  function handleTag_em() {
    $this->out('*', true);
  }
  function handleTag_i() {
    $this->handleTag_em();
  }
  /**
   * handle <strong> and <b> tags
   *
   * @param void
   * @return void
   */
  function handleTag_strong() {
    $this->out('**', true);
  }
  function handleTag_b() {
    $this->handleTag_strong();
  }
  /**
   * handle <h1> tags
   *
   * @param void
   * @return void
   */
  function handleTag_h1() {
    $this->handleHeader(1);
  }
  /**
   * handle <h2> tags
   *
   * @param void
   * @return void
   */
  function handleTag_h2() {
    $this->handleHeader(2);
  }
  /**
   * handle <h3> tags
   *
   * @param void
   * @return void
   */
  function handleTag_h3() {
    $this->handleHeader(3);
  }
  /**
   * handle <h4> tags
   *
   * @param void
   * @return void
   */
  function handleTag_h4() {
    $this->handleHeader(4);
  }
  /**
   * handle <h5> tags
   *
   * @param void
   * @return void
   */
  function handleTag_h5() {
    $this->handleHeader(5);
  }
  /**
   * handle <h6> tags
   *
   * @param void
   * @return void
   */
  function handleTag_h6() {
    $this->handleHeader(6);
  }
  /**
   * number of line breaks before next inline output
   */
  var $lineBreaks = 0;
  /**
   * handle header tags (<h1> - <h6>)
   *
   * @param int $level 1-6
   * @return void
   */
  function handleHeader($level) {
    if ($this->parser->isStartTag) {
      $this->out(str_repeat('#', $level).' ', true);
    } else {
      $this->setLineBreaks(2);
    }
  }
  /**
   * handle <p> tags
   *
   * @param void
   * @return void
   */
  function handleTag_p() {
    if (!$this->parser->isStartTag) {
      $this->setLineBreaks(2);
    }
  }
  /**
   * handle <a> tags
   *
   * @param void
   * @return void
   */
  function handleTag_a() {
    if ($this->parser->isStartTag) {
      $this->buffer();
      if (isset($this->parser->tagAttributes['title'])) {
        $this->parser->tagAttributes['title'] = $this->decode($this->parser->tagAttributes['title']);
      } else {
        $this->parser->tagAttributes['title'] = null;
      }
      $this->parser->tagAttributes['href'] = $this->decode(trim($this->parser->tagAttributes['href']));
      $this->stack();
    } else {
      $tag = $this->unstack();
      $buffer = $this->unbuffer();

      if (empty($tag['href']) && empty($tag['title'])) {
        # empty links... testcase mania, who would possibly do anything like that?!
        $this->out('['.$buffer.']()', true);
        return;
      }

      if ($buffer == $tag['href'] && empty($tag['title'])) {
        # <http://example.com>
        $this->out('<'.$buffer.'>', true);
        return;
      }

      $bufferDecoded = $this->decode(trim($buffer));
      if (substr($tag['href'], 0, 7) == 'mailto:' && 'mailto:'.$bufferDecoded == $tag['href']) {
        if (is_null($tag['title'])) {
          # <mail@example.com>
          $this->out('<'.$bufferDecoded.'>', true);
          return;
        }
        # [mail@example.com][1]
        # ...
        #  [1]: mailto:mail@example.com Title
        $tag['href'] = 'mailto:'.$bufferDecoded;
      }
      # [This link][id]
      foreach ($this->stack['a'] as $tag2) {
        if ($tag2['href'] == $tag['href'] && $tag2['title'] === $tag['title']) {
          $tag['linkID'] = $tag2['linkID'];
          break;
        }
      }
      if (!isset($tag['linkID'])) {
        $tag['linkID'] = count($this->stack['a']) + 1;
        array_push($this->stack['a'], $tag);
      }

      $this->out('['.$buffer.']['.$tag['linkID'].']', true);
    }
  }
  /**
   * handle <img /> tags
   *
   * @param void
   * @return void
   */
  function handleTag_img() {
    if (!$this->parser->isStartTag) {
      return; # just to be sure this is really an empty tag...
    }

    if (isset($this->parser->tagAttributes['title'])) {
      $this->parser->tagAttributes['title'] = $this->decode($this->parser->tagAttributes['title']);
    } else {
      $this->parser->tagAttributes['title'] = null;
    }
    if (isset($this->parser->tagAttributes['alt'])) {
      $this->parser->tagAttributes['alt'] = $this->decode($this->parser->tagAttributes['alt']);
    } else {
      $this->parser->tagAttributes['alt'] = null;
    }

    if (empty($this->parser->tagAttributes['src'])) {
      # support for "empty" images... dunno if this is really needed
      # but there are some testcases which do that...
      if (!empty($this->parser->tagAttributes['title'])) {
        $this->parser->tagAttributes['title'] = ' '.$this->parser->tagAttributes['title'].' ';
      }
      $this->out('!['.$this->parser->tagAttributes['alt'].']('.$this->parser->tagAttributes['title'].')', true);
      return;
    } else {
      $this->parser->tagAttributes['src'] = $this->decode($this->parser->tagAttributes['src']);
    }

    # [This link][id]
    $link_id = false;
    if (!empty($this->stack['a'])) {
      foreach ($this->stack['a'] as $tag) {
        if ($tag['href'] == $this->parser->tagAttributes['src']
            && $tag['title'] === $this->parser->tagAttributes['title']) {
          $link_id = $tag['linkID'];
          break;
        }
      }
    } else {
      $this->stack['a'] = array();
    }
    if (!$link_id) {
      $link_id = count($this->stack['a']) + 1;
      $tag = array(
        'href' => $this->parser->tagAttributes['src'],
        'linkID' => $link_id,
        'title' => $this->parser->tagAttributes['title']
      );
      array_push($this->stack['a'], $tag);
    }

    $this->out('!['.$this->parser->tagAttributes['alt'].']['.$link_id.']', true);
  }
  /**
   * handle <code> tags
   *
   * @param void
   * @return void
   */
  function handleTag_code() {
    if ($this->hasParent('pre')) {
      # ignore code blocks inside <pre>
      return;
    }
    if ($this->parser->isStartTag) {
      $this->buffer();
    } else {
      $buffer = $this->unbuffer();
      # use as many backticks as needed
      preg_match_all('#`+#', $buffer, $matches);
      if (!empty($matches[0])) {
        rsort($matches[0]);

        $ticks = '`';
        while (true) {
          if (!in_array($ticks, $matches[0])) {
            break;
          }
          $ticks .= '`';
        }
      } else {
        $ticks = '`';
      }
      if ($buffer[0] == '`' || substr($buffer, -1) == '`') {
        $buffer = ' '.$buffer.' ';
      }
      $this->out($ticks.$buffer.$ticks, true);
    }
  }
  /**
   * handle <pre> tags
   *
   * @param void
   * @return void
   */
  function handleTag_pre() {
    if ($this->keepHTML && $this->parser->isStartTag) {
      # check if a simple <code> follows
      if (!preg_match('#^\s*<code\s*>#Us', $this->parser->html)) {
        # this is no standard markdown code block
        $this->handleTagToText();
        return;
      }
    }
    $this->indent('    ');
    if (!$this->parser->isStartTag) {
      $this->setLineBreaks(2);
    } else {
      $this->parser->html = ltrim($this->parser->html);
    }
  }
  /**
   * handle <blockquote> tags
   *
   * @param void
   * @return void
   */
  function handleTag_blockquote() {
    $this->indent('> ');
  }
  /**
   * handle <ul> tags
   *
   * @param void
   * @return void
   */
  function handleTag_ul() {
    if ($this->parser->isStartTag) {
      $this->stack();
      if (!$this->keepHTML && $this->lastClosedTag == $this->parser->tagName) {
        $this->out("\n".$this->indent.'<!-- -->'."\n".$this->indent."\n".$this->indent);
      }
    } else {
      $this->unstack();
      if ($this->parent() != 'li' || preg_match('#^\s*(</li\s*>\s*<li\s*>\s*)?<(p|blockquote)\s*>#sU', $this->parser->html)) {
        # dont make Markdown add unneeded paragraphs
        $this->setLineBreaks(2);
      }
    }
  }
  /**
   * handle <ul> tags
   *
   * @param void
   * @return void
   */
  function handleTag_ol() {
    # same as above
    $this->parser->tagAttributes['num'] = 0;
    $this->handleTag_ul();
  }
  /**
   * handle <li> tags
   *
   * @param void
   * @return void
   */
  function handleTag_li() {
    if ($this->parent() == 'ol') {
      $parent =& $this->getStacked('ol');
      if ($this->parser->isStartTag) {
        $parent['num']++;
        $this->out($parent['num'].'.'.str_repeat(' ', 3 - strlen($parent['num'])), true);
      }
      $this->indent('    ', false);
    } else {
      if ($this->parser->isStartTag) {
        $this->out('*   ', true);
      }
      $this->indent('    ', false);
    }
    if (!$this->parser->isStartTag) {
      $this->setLineBreaks(1);
    }
  }
  /**
   * handle <hr /> tags
   *
   * @param void
   * @return void
   */
  function handleTag_hr() {
    if (!$this->parser->isStartTag) {
      return; # just to be sure this really is an empty tag
    }
    $this->out('* * *', true);
    $this->setLineBreaks(2);
  }
  /**
   * handle <br /> tags
   *
   * @param void
   * @return void
   */
  function handleTag_br() {
    $this->out("  \n".$this->indent, true);
    $this->parser->html = ltrim($this->parser->html);
  }
  /**
   * node stack, e.g. for <a> and <abbr> tags
   *
   * @var array<array>
   */
  var $stack = array();
  /**
   * add current node to the stack
   * this only stores the attributes
   *
   * @param void
   * @return void
   */
  function stack() {
    if (!isset($this->stack[$this->parser->tagName])) {
      $this->stack[$this->parser->tagName] = array();
    }
    array_push($this->stack[$this->parser->tagName], $this->parser->tagAttributes);
  }
  /**
   * remove current tag from stack
   *
   * @param void
   * @return array
   */
  function unstack() {
    if (!isset($this->stack[$this->parser->tagName]) || !is_array($this->stack[$this->parser->tagName])) {
      trigger_error('Trying to unstack from empty stack. This must not happen.', E_USER_ERROR);
    }
    return array_pop($this->stack[$this->parser->tagName]);
  }
  /**
   * get last stacked element of type $tagName
   *
   * @param string $tagName
   * @return array
   */
  function & getStacked($tagName) {
    // no end() so it can be referenced
    return $this->stack[$tagName][count($this->stack[$tagName])-1];
  }
  /**
   * set number of line breaks before next start tag
   *
   * @param int $number
   * @return void
   */
  function setLineBreaks($number) {
    if ($this->lineBreaks < $number) {
      $this->lineBreaks = $number;
    }
  }
  /**
   * stores current buffers
   *
   * @var array<string>
   */
  var $buffer = array();
  /**
   * buffer next parser output until unbuffer() is called
   *
   * @param void
   * @return void
   */
  function buffer() {
    array_push($this->buffer, '');
  }
  /**
   * end current buffer and return buffered output
   *
   * @param void
   * @return string
   */
  function unbuffer() {
    return array_pop($this->buffer);
  }
  /**
   * append string to the correct var, either
   * directly to $this->output or to the current
   * buffers
   *
   * @param string $put
   * @return void
   */
  function out($put, $nowrap = false) {
    if (empty($put)) {
      return;
    }
    if (!empty($this->buffer)) {
      $this->buffer[count($this->buffer) - 1] .= $put;
    } else {
      if ($this->bodyWidth && !$this->parser->keepWhitespace) { # wrap lines
        // get last line
        $pos = strrpos($this->output, "\n");
        if ($pos === false) {
          $line = $this->output;
        } else {
          $line = substr($this->output, $pos);
        }

        if ($nowrap) {
          if ($put[0] != "\n" && $this->strlen($line) + $this->strlen($put) > $this->bodyWidth) {
            $this->output .= "\n".$this->indent.$put;
          } else {
            $this->output .= $put;
          }
          return;
        } else {
          $put .= "\n"; # make sure we get all lines in the while below
          $lineLen = $this->strlen($line);
          while ($pos = strpos($put, "\n")) {
            $putLine = substr($put, 0, $pos+1);
            $put = substr($put, $pos+1);
            $putLen = $this->strlen($putLine);
            if ($lineLen + $putLen < $this->bodyWidth) {
              $this->output .= $putLine;
              $lineLen = $putLen;
            } else {
              $split = preg_split('#^(.{0,'.($this->bodyWidth - $lineLen).'})\b#', $putLine, 2, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE);
              $this->output .= rtrim($split[1][0])."\n".$this->indent.$this->wordwrap(ltrim($split[2][0]), $this->bodyWidth, "\n".$this->indent, false);
            }
          }
          $this->output = substr($this->output, 0, -1);
          return;
        }
      } else {
        $this->output .= $put;
      }
    }
  }
  /**
   * current indentation
   *
   * @var string
   */
  var $indent = '';
  /**
   * indent next output (start tag) or unindent (end tag)
   *
   * @param string $str indentation
   * @param bool $output add indendation to output
   * @return void
   */
  function indent($str, $output = true) {
    if ($this->parser->isStartTag) {
      $this->indent .= $str;
      if ($output) {
        $this->out($str, true);
      }
    } else {
      $this->indent = substr($this->indent, 0, -strlen($str));
    }
  }
  /**
   * decode email addresses
   *
   * @author derernst@gmx.ch <http://www.php.net/manual/en/function.html-entity-decode.php#68536>
   * @author Milian Wolff <http://milianw.de>
   */
  function decode($text, $quote_style = ENT_QUOTES) {
    if (version_compare(PHP_VERSION, '5', '>=')) {
      # UTF-8 is only supported in PHP 5.x.x and above
      $text = html_entity_decode($text, $quote_style, 'UTF-8');
    } else {
      if (function_exists('html_entity_decode')) {
        $text = html_entity_decode($text, $quote_style, 'ISO-8859-1');
      } else {
        static $trans_tbl;
        if (!isset($trans_tbl)) {
          $trans_tbl = array_flip(get_html_translation_table(HTML_ENTITIES, $quote_style));
        }
        $text = strtr($text, $trans_tbl);
      }
      $text = preg_replace_callback('~&#x([0-9a-f]+);~i', array(&$this, '_decode_hex'), $text);
      $text = preg_replace_callback('~&#(\d{2,5});~', array(&$this, '_decode_numeric'), $text);
    }
    return $text;
  }
  /**
   * callback for decode() which converts a hexadecimal entity to UTF-8
   *
   * @param array $matches
   * @return string UTF-8 encoded
   */
  function _decode_hex($matches) {
    return $this->unichr(hexdec($matches[1]));
  }
  /**
   * callback for decode() which converts a numerical entity to UTF-8
   *
   * @param array $matches
   * @return string UTF-8 encoded
   */
  function _decode_numeric($matches) {
    return $this->unichr($matches[1]);
  }
  /**
   * UTF-8 chr() which supports numeric entities
   *
   * @author grey - greywyvern - com <http://www.php.net/manual/en/function.chr.php#55978>
   * @param array $matches
   * @return string UTF-8 encoded
   */
  function unichr($dec) {
    if ($dec < 128) {
      $utf = chr($dec);
    } else if ($dec < 2048) {
      $utf = chr(192 + (($dec - ($dec % 64)) / 64));
      $utf .= chr(128 + ($dec % 64));
    } else {
      $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
      $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
      $utf .= chr(128 + ($dec % 64));
    }
    return $utf;
  }
  /**
   * UTF-8 strlen()
   *
   * @param string $str
   * @return int
   *
   * @author dtorop 932 at hotmail dot com <http://www.php.net/manual/en/function.strlen.php#37975>
   * @author Milian Wolff <http://milianw.de>
   */
  function strlen($str) {
    if (function_exists('mb_strlen')) {
      return mb_strlen($str, 'UTF-8');
    } else {
      return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $var_empty);
    }
  }
  /**
  * wordwrap for utf8 encoded strings
  *
  * @param string $str
  * @param integer $len
  * @param string $what
  * @return string
  */
  function wordwrap($str, $width, $break, $cut = false){
    if (!$cut) {
      $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){1,'.$width.'}\b#';
    } else {
      $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.'}#';
    }
    $return = '';
    while (preg_match($regexp, $str, $matches)) {
      $string = $matches[0];
      $str = ltrim(substr($str, strlen($string)));
      if (!$cut && isset($str[0]) && in_array($str[0], array('.', '!', ';', ':', '?', ','))) {
        $string .= $str[0];
        $str = ltrim(substr($str, 1));
      }
      $return .= $string.$break;
    }
    return $return.ltrim($str);
  }
  /**
   * check if current node has a $tagName as parent (somewhere, not only the direct parent)
   *
   * @param string $tagName
   * @return bool
   */
  function hasParent($tagName) {
    return in_array($tagName, $this->parser->openTags);
  }
  /**
   * get tagName of direct parent tag
   *
   * @param void
   * @return string $tagName
   */
  function parent() {
    return end($this->parser->openTags);
  }
}

/* =================================== xiuno 封装整理 ============================= */

class xn_markdown {
	
	public static function markdown2html($text) {
		static $parser;
		if (!isset($parser)) {
			$parser = new Markdown_Parser;
		}
		return $parser->transform($text);
	}
	
	public static function html2markdown($s) {
		static $md;
		if (!isset($md)) {
			$md = new Markdownify();
		}
		return $md->parseString($s);
	}
}


// 测试代码
/*$s = '
<ul>
<li>aaa</li>
<li>aaa</li>
<li>aaa</li>
<li>aaa</li>
</ul>

<h2>This is an H2</h2>
xxxx
';

$s = xn_markdown::html2markdown($s);
echo $s;
$s = xn_markdown::markdown2html($s);
echo $s;*/
?>