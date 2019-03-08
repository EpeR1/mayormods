 KoschtIT Image Gallery
 by Konstantin Tabere


 Latest version
 --------------

 The most recent script version can be downloaded at <http://koschtit.tabere.net/en/>.
 Beta versions will be available in the script forum.


 Documentation
 -------------

 The script documentation is also available online at <http://koschtit.tabere.net/en/documentation/install>.
 If you want to add the script to your website, follow these steps:

 1. Upload the script to your webserver. Please note, that the script needs PHP5 with GD2 enabled.

 2. Set the correct DOCTYPE for your webpage on the first line of your source code.
    If you have HTML use:
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    If you have XHTML use:
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
 3. Add: <?php include_once("...../ki_include.php"); ?> into your header ( between <head> and </head> ).
    Make sure the path to the "ki_include.php" is entered correctly.

 4. Add: <div class="koschtitgallery" title="sample"></div> to the spot where your gallery should be.
    Edit 'title' to specify which gallery folder you want to display.

 5. Create gallery folders inside the "ki_galleries"-folder. For each new gallery a seperate folder is neccessary.
    This is the place where your images will go to. A "sample" gallery comes with this package.
    
 6. You can open the admin panel for each gallery directly on your website. Therefore add an additional parameter "?admin=admin" to the url
    e.g. "http://somedomain.com/mygallery/?admin=admin". Login to the admin panel with the default password: "password"
    
 7. Change the default admin/user-username and password for both admins and users in the "Settings"-menu. Now you can upload your images either through the "Upload Images"-menu
    or if you want you can use a FTP-client.
 
 8. Edit further parameters through the "Settings"-menu. The "ki_setup.php" - config file is applied to all galleries. If you want to make
    custom settings for individual galleries you will need to select the "galleryfolder_ki_setup.php" - config file.
    
 9. You can label pictures (image descriptions) when logged in as admin. Therefore just open the picture and write your text in the box
    below the image. To save the picture comments press 'CTRL + Enter'.

 If the script gives you warning messages you can disable them with the "$show_warnings"-parameter.


 Licensing
 ---------

 The script is free and available for everyone without limitations. If you are using the script in a commercial project a donation
 would be strongly appreciated. You can make a Donation via PayPal (they also accept credit cards) on my website <http://koschtit.tabere.net/en/>.


 Contact
 -------

 If you want to contact me write me an E-mail to: kkokus [at] web [dot] de .
 Please use the forum at <http://koschtit.tabere.net/forum/> in case you need script support.
