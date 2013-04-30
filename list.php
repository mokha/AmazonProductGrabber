<?php
/*
 * Develop a php code to grab products from Amazon. 
 * The grabber should be able to start grabbing products from any Amazon product page. 
 * The code should be able to grab product Title, technical details, and a single picture of the product. 
 * All this data should be captured and stored on a local server.
 * 
 * the success criteria for this code is to be able to query and 
 * grab 10,000 products from different categories from Amazon, 
 * (electronics, or books, or food, anything is possible and should be tested.)
 */
require_once 'config.php';
require_once 'Amazon.php';

$count = Product::count();

?>

<?php include_once 'header.php'; ?>
<div class="col_12" style="margin-top:100px;">
    <a href="index.php"><h1 class="center">
        <p><i class="icon-bolt"></i></p>
        Amazon Grabber</h1></a>
    <h4 style="color:#999;margin-bottom:40px;" class="center">Loading depends on Internet speed</h4>
    <a href="list.php"><h4 style="color:red;margin-bottom:40px;" class="center"><span id="count"><?php echo $count; ?></span> Products Grabbed (Click Here to List Them)</h4></a>
    <div class="column center">
        <form method="get" id="grabForm" action="index.php">
            <label for="field-keywords">Search</label>
            <input id="field-keywords" name="field-keywords" type="text" placeholder="Search" />
            <label for="url">Select Category</label>
            <select name="url" id="searchDropdownBox" title="Search in">
                <option value="search-alias=aps" selected="selected" >All Departments</option>
                <option value="search-alias=instant-video">Amazon Instant Video</option>
                <option value="search-alias=appliances">Appliances</option>
                <option value="search-alias=mobile-apps" current="parent">Apps for Android</option>
                <option value="search-alias=arts-crafts">Arts, Crafts &amp; Sewing</option>
                <option value="search-alias=automotive">Automotive</option>
                <option value="search-alias=baby-products">Baby</option>
                <option value="search-alias=beauty">Beauty</option>
                <option value="search-alias=stripbooks">Books</option>
                <option value="search-alias=mobile">Cell Phones &amp; Accessories</option>
                <option value="search-alias=apparel">Clothing &amp; Accessories</option>
                <option value="search-alias=collectibles">Collectibles</option>
                <option value="search-alias=computers">Computers</option>
                <option value="search-alias=financial">Credit Cards</option>
                <option value="search-alias=electronics">Electronics</option>
                <option value="search-alias=gift-cards">Gift Cards Store</option>
                <option value="search-alias=grocery">Grocery &amp; Gourmet Food</option>
                <option value="search-alias=hpc">Health &amp; Personal Care</option>
                <option value="search-alias=garden">Home &amp; Kitchen</option>
                <option value="search-alias=industrial">Industrial &amp; Scientific</option>
                <option value="search-alias=jewelry">Jewelry</option>
                <option value="search-alias=digital-text">Kindle Store</option>
                <option value="search-alias=magazines">Magazine Subscriptions</option>
                <option value="search-alias=movies-tv">Movies &amp; TV</option>
                <option value="search-alias=digital-music">MP3 Music</option>
                <option value="search-alias=popular">Music</option>
                <option value="search-alias=mi">Musical Instruments</option>
                <option value="search-alias=office-products">Office Products</option>
                <option value="search-alias=lawngarden">Patio, Lawn &amp; Garden</option>
                <option value="search-alias=pets">Pet Supplies</option>
                <option value="search-alias=shoes">Shoes</option>
                <option value="search-alias=software">Software</option>
                <option value="search-alias=sporting">Sports &amp; Outdoors</option>
                <option value="search-alias=tools">Tools &amp; Home Improvement</option>
                <option value="search-alias=toys-and-games">Toys &amp; Games</option>
                <option value="search-alias=videogames">Video Games</option>
                <option value="search-alias=watches">Watches</option>
            </select>
            <input class="button blue" type="submit" value="Grab" id="grab" name="submit"/>
        </form>
        <div id="products">
            <?php 
                $products = Product::getAll();
                $c = 0;
                foreach($products as $product){
                    if($c == 0){
                        echo'<div class="clearfix">';
                    }
                    echo '<div class="col_3 left"><img class="caption" title="' . $product->title . '" src="img/' .  $product->picture . '" width="250" height="250" /></div>';
                    $c++;
                    if($c == 4){
                        $c = 0;
                        echo '</div>';
                    }
                }
            ?>
        </div>
    </div>

<?php include_once 'footer.php'; ?>