Single item pagination
======================
A [Zenphoto](http://www.zenphoto.org) plugin to provide extra functionality for numbererd pagelist of single gallery items (single image, album and Zenpage news article and pages). Similar to the standard template function `printPagelistWithNav()` or Zenpage's `printZenpagePagelistWithNav()`:
`Example: prev 1 2 3 4 next`

Put the file in your `/plugins` folder and modify your theme to use it. 

##Usage

###a) On your theme's image.php:
Add the function `printPrevNextItemPageListWithNav('image')` to replace the standard single image prev/next (`getPrevImageURL()`/`getNextImageURL()`) to print a numbered page list of the image within the current album.
 
###b) On your theme's image.php or album.php:
Add the function `printPrevNextItemPageListWithNav('album')` to add an extra navigation to acccess the prev/next albumson the same level if you are within an album. This is an extended usage to standard template functions like `getPrevAlbumURL()` and `getNextAlbumURL()`.
 
###c) On your theme's news.php within is_NewsArticle():
Add the function `printPrevNextItemPageListWithNav('article')` to replace the standard single article prev/next (`printPrevNewsLink()`/`printNextNewsLink()`).
 
###d) On your theme's pages.php:
Add the function `printPrevNextItemPageListWithNav('page')`
This has no standard equivalent function and lets you move through all the pages on the same level.
 
*Requirements c) und d):* Theme with Zenpage plugin support and the Zenpage plugin being enabled.
