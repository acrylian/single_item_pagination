<?php

/**
 * Provides extra functionality for numbered pagelist of single gallery items (single image, album and Zenpage news article and pages) 
 * similar to the standard template function printPagelistWithNav() or Zenpage's printZenpagePagelistWithNav():
 * Example: prev 1 2 3 4 next
 *
 * a) On your theme's image.php:
 * Add the function printPrevNextItemPageListWithNav('image') to replace the standard single image prev/next
 * (getPrevImageURL()/getNextImageURL()) to print a numbered page list of the image within the current album.
 *
 * b) On your theme's image.php or album.php:
 * Add the function printPrevNextItemPageListWithNav('album') to add an extra navigation to acccess the prev/next albums
 * on the same level if you are within an album. This is an extended usage to standard template functions like
 * getPrevAlbumURL() and getNextAlbumURL().
 *
 * c) On your theme's news.php within is_NewsArticle():
 * Add the function printPrevNextItemPageListWithNav('article')
 * to replace the standard single article prev/next (printPrevNewsLink()/printNextNewsLink()).
 *
 * d) On your theme's pages.php:
 * Add the function printPrevNextItemPageListWithNav('page')
 * This has no standard equivalent function and lets you move through all the pages on the same level.
 *
 * Requirements c) und d): Theme with Zenpage plugin support and the Zenpage plugin being enabled.
 *
 * @author Malte Müller (acrylian) <info@maltem.de>
 * @copyright 2018 Malte Müller
 * @license GPL v3 or later
 * @package plugins
 * @subpackage media
 */
$plugin_description = gettext('Provides extra functionality for numbered pagination of single items (images, albums, Zenpage articles and pages).');
$plugin_author = "Malte Müller (acrylian)";
$plugin_version = '1.2';

/**
 * Prints the single image page navigation with prev/next links and the page number list
 * @param string $mode 'image', 'album' or for the Zenpage CMS plugin 'article', 'page'
 * @param string $prev The prev page link text
 * @param string $next The next page link text
 * @param bool $nextprev If the prev/next links should be printed
 * @param string $class The CSS class for the disabled link
 * @param bool $firstlast If the first/last links should be printed
 * @param int $navlen Length
 *
 * @return string
 */
function printPrevNextItemPagelistWithNav($mode = 'image', $prevtext = 'prev', $nexttext = 'next', $nextprev = true, $class = 'pagelist', $firstlast = true, $navlen = 7) {
	global $_zp_gallery, $_zp_gallery_page, $_zp_zenpage, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_album, $_zp_current_image, $_zp_current_search;
	switch ($mode) {
		case 'image':
			$total = getNumImages();
			$images = $_zp_current_album->getImages(0);
			$current = imageNumber();
			break;
		case 'album':
			if ($_zp_current_album->getParent()) {
				$currentalbum = $_zp_current_album->getParent();
			} else {
				$currentalbum = $_zp_gallery;
			}
			$albums = $currentalbum->getAlbums(0);
			$total = $currentalbum->getNumAlbums();
			$current = '';
			// get the key number of the current album
			foreach ($albums as $album) {
				if ($album == $_zp_current_album->name) {
					$current = key($albums) + 1;
				}
				next($albums);
			}
			break;
		case 'article':
			$all_articles = $_zp_zenpage->getArticles('', '', NULL, true);
			$total = count($all_articles);
			$count = 0;
			foreach ($all_articles as $article) {
				$newsobj = new ZenpageNews($article['titlelink']);
				$count++;
				$title[$count] = $newsobj->getTitle();
				$titlelink[$count] = $newsobj->getTitlelink();
				if ($titlelink[$count] == $_zp_current_zenpage_news->getTitlelink()) {
					$current = $count;
				}
			}
			//previous article url
			$prev = $current - 1;
			$prevarticle = '';
			if ($prev > 0) {
				$articlelink = getNewsURL($title[$prev]);
				$articletitle = $title[$prev];
				$prevarticle = getNewsURL($titlelink[$prev]);
			}
			// next article url
			$next = $current + 1;
			$nextarticle = '';
			if ($next <= $count) {
				$articlelink = getNewsURL($title[$next]);
				$articletitle = $title[$next];
				$nextarticle = getNewsURL($titlelink[$next]);
			}
			break;
		case 'page':
			if ($_zp_current_zenpage_page->getParentID()) {
				$parents = $_zp_current_zenpage_page->getParents();
				$parents = array_reverse($parents); // reverse so the parent we want is always index 0
				$parent = $parents[0];
				$basepage = new Zenpagepage($parent);
				$pages = $basepage->getPages();
			} else {
				$basepage = $_zp_zenpage;
				$allpages = $basepage->getPages();
				$pages = array();
				$count = '';
				// We want only the top level here!
				foreach ($allpages as $page) {
					$obj = new ZenpagePage($page['titlelink']);
					if (!$obj->getParentID()) {
						$count++;
						$pages[$count] = $obj->getTitlelink();
					}
				}
			}
			$total = count($pages);
			$count = 0;
			foreach ($pages as $page) {
				$pageobj = new ZenpagePage($page);
				$count++;
				$title[$count] = $pageobj->getTitle();
				$titlelink[$count] = $pageobj->getTitlelink();
				if ($titlelink[$count] == $_zp_current_zenpage_page->getTitlelink()) {
					$current = $count;
				}
			}
			//previous page url
			$prev = $current - 1;
			$prevpage = '';
			if ($prev > 0) {
				$pagelink = getPageURL($title[$prev]);
				$pagetitle = $title[$prev];
				$prevpage = getPageURL($titlelink[$prev]);
			}
			// next article url
			$next = $current + 1;
			$nextpage = '';
			if ($next <= $count) {
				$pagelink = getPageURL($title[$next]);
				$pagetitle = $title[$next];
				$nextpage = getPageURL($titlelink[$next]);
			}
			break;
	}
	if ($total > 1) {
		if ($navlen == 0) {
			$navlen = $total;
		}
		$extralinks = 2;
		if ($firstlast)
			$extralinks = $extralinks + 2;
		$len = floor(($navlen - $extralinks) / 2);
		$j = max(round($extralinks / 2), min($current - $len - (2 - round($extralinks / 2)), $total - $navlen + $extralinks - 1));
		$ilim = min($total, max($navlen - round($extralinks / 2), $current + floor($len)));
		$k1 = round(($j - 2) / 2) + 1;
		$k2 = $total - round(($total - $ilim) / 2);
		echo '<ul class="'. $class.'">';
		$hasprev = false;
		$link = '';
		switch ($mode) {
			case 'image':
				if (hasPrevImage()) {
					$hasprev = true;
					$link = getPrevImageURL();
				}
				break;
			case 'album':
				if ($current != 1) {
					$hasprev = true;
					$obj = $_zp_current_album->getPrevAlbum();
					$link = $obj->getLink();
				}
				break;
			case 'article':
				if (!empty($prevarticle)) {
					$hasprev = true;
					$link = $prevarticle;
				}
				break;
			case 'page':
				if (!empty($prevpage)) {
					$hasprev = true;
					$link = $prevpage;
				}
				break;
		}
		if ($nextprev) {
			echo '<li class="prev">';
			if ($hasprev) {
				echo '<a href="'. html_encode($link) . '">' . html_encode($prevtext) . '</a>';
			} else {
				echo '<span class="disabledlink">' . html_encode($prevtext) . '</span>'. "\n";
			}
			echo "</li>\n";
		}
		if ($firstlast) {
			echo '<li class="' . ($current == 1 ? 'current' : 'first') . '">';
			if ($current == 1) {
				echo "1";
			} else {
				switch ($mode) {
					case 'image':
						if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED)) {
							$image = $_zp_current_search->getImage(0);
							$link = $image->getLink();
						} else {
							$image = $_zp_current_album->getImage(0);
							$link = $image->getLink();
						}
						break;
					case 'album':
						$firstalbum = $albums[0];
						$obj = Albumbase::newAlbum($firstalbum);
						$link = $obj->getLink();
						break;
					case 'article':
						$link = getNewsURL($titlelink[1]);
						break;
					case 'page':
						$link = getPageURL($titlelink[1]);
						break;
				}
				echo '<a href="' . html_encode($link) . '" title="' . gettext("Page") . ' 1">1</a>'."\n";
			}
			echo "</li>\n";
			if ($j > 2) {
				$linktext = ($j - 1 > 2) ? '...' : $k1;
				echo "<li>";
				switch ($mode) {
					case 'image':
						if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED)) {
							$img = $_zp_current_search->getImage($k1 - 1);
							$link = $img->getLink();
						} else {
							$image = $_zp_current_album->getImage($k1 - 1);
							$link = $image->getLink();
						}
						break;
					case 'album':
						$album = $albums[$k1 - 1];
						$obj = Albumbase::newAlbum($album);
						$link = $obj->getLink();
						break;
					case 'article':
						$link = getNewsURL($titlelink[$k1]);
						break;
					case 'page':
						$link = getPageURL($titlelink[$k1]);
						break;
				}
				echo '<a href="' . html_encode($link) . '" title="' . sprintf(ngettext('Image %u', 'Page %u', $k1), $k1) . '">' . html_encode($linktext) . '</a>';
				echo "</li>\n";
			}
		} // firstlast end
		for ($i = $j; $i <= $ilim; $i++) {
			echo "<li" . (($i == $current) ? " class=\"current\"" : "") . ">";
			if ($i == $current) {
				echo $i;
			} else {
				switch ($mode) {
					case 'image':
						if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED)) {
							$img = $_zp_current_search->getImage($i - 1);
							$link = $img->getLink();
						} else {
							if ($i == 1) {
								$image = $_zp_current_album->getImage(0);
								$link = $image->getLink();
							} else if ($i == $total) {
								$image = $_zp_current_album->getImage($_zp_current_album->getNumImages() - 1);
								$link = $image->getLink();
							} else {
								$image = $_zp_current_album->getImage($i - 1);
								$link = $image->getLink();
							}
						}
						break;
					case 'album':
						$album = $albums[$i - 1];
						$obj = Albumbase::newAlbum($album);
						$link = $obj->getLink();
						break;
					case 'article':
						if ($i == 1 AND getOption("zenpage_zp_index_news")) {
							$link = getNewsIndexURL();
						} else {
							$link = getNewsURL($titlelink[$i]);
						}
						break;
					case 'page':
						$link = getPageURL($titlelink[$i]);
						break;
				}
				echo "<a href='" . html_encode($link) . "' title='" . sprintf(ngettext('Image %1$u', 'Page %1$u', $i), $i) . "'>" . $i . "</a>\n";
			}
			echo "</li>\n";
		}
		if ($i < $total) {
			echo '<li>';
			$linktext = ($total - $i > 1) ? '...' : $k2;
			switch ($mode) {
				case 'image':
					if (!is_null($_zp_current_search) && !in_context(ZP_ALBUM_LINKED)) {
						$img = $_zp_current_search->getImage($k2 - 1);
						$link = $img->getLink();
					} else {
						$image = $_zp_current_album->getImage($k2 - 1);
						$link = $image->getLink();
					}
					break;
				case 'album':
					$album = $albums[$k2 - 1];
					$obj = Albumbase::newAlbum($album);
					$link = $obj->getLink();
					break;
				case 'article':
					$link = getNewsURL($titlelink[$k2]);
					break;
				case 'page':
					$link = getPageURL($titlelink[$k2]);
					break;
			}
			echo '<a href="' . html_encode($link) . '" title="' . sprintf(ngettext('Image %u', 'Page %u', $k2), $k2) . '">' . html_encode($linktext) . '</a>'. "\n";
			echo "</li>\n";
		}
		if ($firstlast && $i <= $total) {
			echo "\n" . '<li class="last">';
			if ($current == $total) {
				echo $total;
			} else {
				switch ($mode) {
					case 'image':
						if ($_zp_current_album->isDynamic()) {
							$imgindex = $total - 1;
							$img = $_zp_current_album->getImage($imgindex);
							$link = $img->getLink();
						} else {
							$img = $_zp_current_album->getImage($_zp_current_album->getNumImages() - 1);
							$link = $img->getLink();
						}
						break;
					case 'album':
						$album = $albums[($total - 1)];
						$obj = Albumbase::newAlbum($album);
						$link = $obj->getLink();
						break;
					case 'article':
						$link = getNewsURL($titlelink[$total]);
						break;
					case 'page':
						$link = getPageURL($titlelink[$total]);
						break;
				}
				echo '<a href="' . html_encode($link) . '" title="' . sprintf(ngettext('Image {%u}', 'Page {%u}', $total), $total) . '">' . $total . '</a>';
			}
			echo '</li>';
		}
		echo "\n" . '<li class="next">';
		$hasnext = false;
		$link = '';
		switch ($mode) {
			case 'image':
				if (hasNextImage()) {
					$hasnext = true;
					$link = getNextImageURL();
				}
				break;
			case 'album':
				if ($current != $total) {
					$hasnext = true;
					$album = $albums[$current];
					$obj = Albumbase::newAlbum($album);
					$link = $obj->getLink();
				}
				break;
			case 'article':
				if (!empty($nextarticle)) {
					$hasnext = true;
					$link = $nextarticle;
				}
				break;
			case 'page':
				if (!empty($nextpage)) {
					$hasnext = true;
					$link = $nextpage;
				}
				break;
		}
		if ($hasnext) {
			echo '<a href="' . html_encode($link) . '">' . $nexttext . '</a>';
		} else {
			echo '<span class="disabledlink">' . $nexttext . '</span>'."\n";
		}
		echo "</li>\n";
		echo '</ul>';
	}
}