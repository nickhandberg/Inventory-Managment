<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale = 1">
    <link rel="stylesheet" href="style.css">
    <!-- index.php - Home Page of the product management system
    Nicholas Handberg
    Written:   11/18/2021
    Revised:    12/4/2021
    -->
    <title>Home</title>
    <?php

        // Local host server credentials
        define("SERVER_NAME","localhost");
        define("DBF_USER_NAME", "root");
        define("DBF_PASSWORD", "mysql");
        define("DATABASE_NAME", "guitarShopInventory");

        // Create the connection object for the database
        $conn = new mysqli(SERVER_NAME, DBF_USER_NAME, DBF_PASSWORD);

        // Checks the connection
        if ($conn -> connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Selects the created database
        $conn -> select_db(DATABASE_NAME);

        clearInputFields( );

        // Constants for table format
        define('PRODUCT','0');
        define('DEPARTMENT','1');
        define('STYLE','2');

        $selectedBtn = PRODUCT;
        $flag = false;
        $sqlDefault = 'SELECT  prod.productName AS "Name", prod.productQty AS "Qty.", prod.productPrice AS "Price", 
                        prod.productPage AS "Product Page", prod.onSale AS "On Sale", style.style AS "Style", dep.departmentName AS "Department"
                FROM product prod
                JOIN productStyle ps
                ON prod.product_id = ps.product_id
                JOIN style 
                ON ps.style_id = style.style_id
                JOIN department dep
                ON prod.department_id = dep.department_id
                ORDER BY prod.productName';
        $sql = $sqlDefault;

        // Checks if the user has already visted the page
        if(array_key_exists('hidIsReturning', $_POST)){
            
            // Checks if the user selected an option from the select box 
            if(isset($_POST['lstProduct']) && !($_POST['lstProduct'] == 'new')){
            $flag = true;
            // Gets the product information from the product, style, and department tables
            $sql = 'SELECT  prod.product_id AS ID, prod.productName AS "Name", prod.productQty AS "Qty", prod.productPrice AS "Price", 
                            prod.productPage AS "Product Page", prod.onSale AS "On Sale", style.style AS "Style", dep.departmentName AS "Department"
                    FROM product prod
                    JOIN productStyle ps
                    ON prod.product_id = ps.product_id
                    JOIN style 
                    ON ps.style_id = style.style_id
                    JOIN department dep
                    ON prod.department_id = dep.department_id
                    WHERE prod.product_id = ?';
            // Set up a prepared statement
            if($stmt = $conn->prepare($sql)) {
                // Bind the parameters
                $stmt->bind_param("i", $_POST['lstProduct']);
                if($stmt->errno) {
                    displayMessage("stmt prepare( ) had error.", "red" ); 
                }
                // Execute the query
                $stmt->execute();
                if($stmt->errno) {
                    displayMessage("Could not execute prepared statement", "red" );
                }
                // Download all the rows into a cache
                $stmt->store_result( );

                // Bind result variables
                $stmt->bind_result($productId, $name, $qty, $price, $page, $onSale, $style, $department);

                // Fetch the value - returns the next row in the result set
                while($stmt->fetch( )) {
                    // Creates array using the data from the item selected from the database
                    $thisProduct = [
                        "product_id" => $productId,
                        "Name" => $name,
                        "Qty" => $qty,
                        "Price" => $price,
                        "Page" => $page,
                        "onSale" => $onSale,
                        "Department" => $department,
                        "Style" => $style
                        ];
                }
                // Free results
                $stmt->free_result( );

                // Close the statement
                $stmt->close( );
                }
            }

            /*********************************************************** */ 
            // CRUD OPERATIONS  
            /*********************************************************** */
            if(isset($_POST['btnSubmit'])){
                switch($_POST['btnSubmit']){
                    /*********************************************************** */ 
                    // DELETE  
                    /*********************************************************** */
                    case 'delete':
                        //Make sure a product has been selected.
                        if($_POST["txtName"] == "") {
                            displayMessage("Please select a product name.", "red");
                        } else {
                            // Closes and creates new connection
                            mysqli_close($conn);
                            createConnection();
                            // SQL string to call stored procedure
                            $sql2 = 'call deleteProduct(?)';

                            // Set up prepared using the sql string
                            if($stmt = $conn->prepare($sql2)) {
                                // Bind the parameters (1 int)
                                $stmt->bind_param("i", $thisProduct["product_id"]);
                                if($stmt->errno) {
                                    displayMessage("Error while binding parameters","red"); 
                                }
                                
                                // Execute the prepared statement
                                $stmt->execute();
                                if($stmt->errno) {
                                    displayMessage("Error while executing prepared statement","red");
                                }
                                else{
                                    displayMessage($thisProduct['Name'] . " Deleted.", "green");
                                }
                                
                                // Free results
                                $stmt->free_result( );
                                $stmt->close( );  
                            }
                            // Close the stored procedure connection and reopen a new one
                            // for other SQL calls
                            mysqli_close($conn);
                            createConnection();
                        }
                        // Clears the input fields
                        clearInputFields( );

                        // Restores sql to default product view for display later
                        $sql = $sqlDefault;
                        $flag = false;
                    break;

                    /*********************************************************** */ 
                    // ADD  
                    /*********************************************************** */
                    case 'new':

                        // Closes and creates new connection
                        mysqli_close($conn);
                        createConnection();
                        
                        // SQL string to call checkDuplicate() stored procedure
                        $sql2 = "call checkDuplicate(?)";

                        // Set up prepared using the sql string
                        if($stmt = $conn->prepare($sql2)) {
                            
                            // Bind the parameters (3 strings)
                            $stmt->bind_param("s", $_POST['txtName']) ;
                            if($stmt->errno) {
                            displayMessage("Error while binding parameters","red"); 
                            }
                            // Execute prepared statement
                            $stmt->execute();
                            if($stmt->errno) {
                            displayMessage("error while executing prepared statement","red" );
                            }
                            // Result stored so we can get num_rows
                            $stmt->store_result( );
                            
                            // Gets the num_rows from the result
                            $totalCount = $stmt->num_rows;

                            // Free results
                            $stmt->free_result( );
                            $stmt->close( );

                            mysqli_close($conn);
                            createConnection();
                        }

                        // Checks if product is already added (result from query)
                        if($totalCount > 0) {
                            displayMessage("This product is already added.", "red");
                        }  
                        else {
                            // Checks for empty fields that are required
                            if ($_POST['txtName']=="" 
                                || $_POST['txtQty']==""
                                || $_POST['txtPrice']==""
                                || !isset($_POST['optDepartment'])
                                || !isset($_POST['optStyle'])) {
                            displayMessage("Please fill in the required fields.", "red");
                            }
                            else {
                                // Gets if the onSale checkbox has been checked.
                                // If not, defaults to 0 (false)
                                $onSale = 0;
                                if(isset($_POST['chkOnSale'])){
                                    $onSale = 1;
                                }

                                // Encodes URL
                                $encodedURL = urlencode($_POST['txtPage']);

                                $productName = $conn -> real_escape_string($_POST['txtName']);

                                // Closes and creates new connection
                                mysqli_close($conn);
                                createConnection();

                                // SQL string to call addProduct() stored procedure
                                $sql2 = "call addProduct(?,?,?,?,?,?,?)";

                                // Set up prepared using the sql string
                                if($stmt = $conn->prepare($sql2)) {
                                    // Bind the parameters (1 int 4 strings)
                                    $stmt->bind_param("sidsiii", $productName, $_POST['txtQty'], $_POST['txtPrice'],
                                                                $encodedURL,$onSale, $_POST['optDepartment'],$_POST['optStyle']);
                                    if($stmt->errno) {
                                    displayMessage("Error while binding parameters","red"); 
                                    }
                                    
                                    // Execute the prepared statement
                                    $stmt->execute();
                                    if($stmt->errno) {
                                    displayMessage("Error while executing prepared statement","red");
                                    }
                                    else{
                                        displayMessage($_POST['txtName'] . " Added.", "green"); 
                                    }
                                    
                                    // Free results
                                    $stmt->free_result( );
                                    $stmt->close( );
                                    
                                }
                                // Close the stored procedure connection and reopen a new one
                                // for other SQL calls
                                mysqli_close($conn);
                                createConnection();
                            }
                            // Clears the input fields
                            clearInputFields( );
                        }
                    break;
                    /*********************************************************** */ 
                    // UPDATE 
                    /*********************************************************** */ 
                    case 'update':
                        // Check for empty name 
                        if($_POST["txtName"] == "") {
                            displayMessage("Please select a product name.", "red");
                        }
                        else {
                            // Gets if the onSale checkbox has been checked.
                            // If not, defaults to 0 (false)
                            $onSale = 0;
                                if(isset($_POST['chkOnSale'])){
                                    $onSale = 1;
                                }
                            $encodedURL = urlencode($_POST['txtPage']);

                            // Closes and creates new connection
                            mysqli_close($conn);
                            createConnection();

                            // SQL string to call addProduct() stored procedure
                            $sql2 = "call updateProduct(?,?,?,?,?,?,?,?)";

                            // Set up prepared using the sql string
                            if($stmt = $conn->prepare($sql2)) {
                                // Bind the parameters (1 int 4 strings)
                                $stmt->bind_param("isidsiii",$thisProduct['product_id'], $_POST['txtName'], $_POST['txtQty'], $_POST['txtPrice'],
                                                            $encodedURL,$onSale, $_POST['optDepartment'],$_POST['optStyle']);
                                if($stmt->errno) {
                                displayMessage("Error while binding parameters","red"); 
                                }
                                
                                // Execute the prepared statement
                                $stmt->execute();
                                if($stmt->errno) {
                                displayMessage("Error while executing prepared statement","red");
                                }
                                else{
                                    displayMessage($_POST['txtName'] . " Updated.", "green"); 
                                }
                                
                                // Free results
                                $stmt->free_result( );
                                $stmt->close( );
                                
                            }
                            // Close the stored procedure connection and reopen a new one
                            // for other SQL calls
                            mysqli_close($conn);
                            createConnection();
                        }
                        // Clears the input fields
                        clearInputFields( );
                    break;
                }
            }
            
            /*********************************************************** */ 
            // CHANGE TABLE DISPLAYED  | USING TOP BUTTONS
            /*********************************************************** */
            // Checks if the lstDisplay is set in the POST array
            if(isset($_POST['btnArray'])){
                // Gets the value of the option the user selected
                $selection = $_POST['btnArray'];
                
                // Switch statement for the selection. Used to set the table format and the sql statement
                switch($selection){
                    case "product": {
                        $selectedBtn = PRODUCT;
                        $sql = 'SELECT  prod.productName AS "Name", prod.productQty AS "Qty.", prod.productPrice AS "Price", 
                                        prod.productPage AS "Product Page", prod.onSale AS "On Sale", style.style AS "Style", dep.departmentName AS "Department"
                                FROM product prod
                                JOIN productStyle ps
                                ON prod.product_id = ps.product_id
                                JOIN style 
                                ON ps.style_id = style.style_id
                                JOIN department dep
                                ON prod.department_id = dep.department_id
                                ORDER BY prod.productName';
                        break;
                    }
                    case "department": {
                        $selectedBtn = DEPARTMENT;
                        $sql= " SELECT department.departmentName AS 'Department' 
                                FROM department 
                                ORDER BY departmentName";
                        break;
                    }
                    case "style": {
                        $selectedBtn = STYLE;
                        $sql = "SELECT style.style AS 'Style' 
                                FROM style
                                ORDER BY style";
                        break;
                    }
                    default: echo $selection . 'is not valid. <br/>';
                }
            }
        }
        /****************************************************************** */
        // createConnection( ) - Create a database connection
        /*******************************************************************/
        function createConnection( ) {
            global $conn;
            // Create connection object
            $conn = new mysqli(SERVER_NAME, DBF_USER_NAME, DBF_PASSWORD);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 
            // Select the database
            $conn->select_db(DATABASE_NAME);
        }

        /*********************************************************************
            displayMessage() - Displays a message to the screen
            Parameters: $msg -   Message to be displayed
                        $color - color of the message to be displayed
        **********************************************************************/
        function displayMessage($msg, $color) {
            echo "<p><strong style='color:" . $color . ";'>" . $msg . "</strong></p>";
        }

        /*********************************************************************
             clearInputFields( ) - Clears the array to clear the input fields
        **********************************************************************/
        function clearInputFields( ) {
            global $thisProduct;
            $thisProduct['product_id'] = "";
            $thisProduct['Name']  = "";
            $thisProduct['Qty']  = "";
            $thisProduct['Price']  = "";
            $thisProduct['Page'] = "";
            $thisProduct['onSale']= "";
            $thisProduct['Department']= "";
            $thisProduct['Style']= "";
        }

        /***************************************************************************************************************************
            DISPLAY TABLE
            Description: Displays the table using the result from the query
            Parameters: $sql: statement to run and retrieve result from
        ****************************************************************************************************************************/
        function displayTable(){
            global $conn;
            global $sql;
            global $flag;
            
            // ITEM SELECTED
            if($flag){
            // Set up a prepared statement
            if($stmt = $conn->prepare($sql)) {
                // Pass the parameters
                $stmt->bind_param("i", $_POST['lstProduct']);
                if($stmt->errno) {
                    displayMessage("stmt prepare( ) had error.", "red" ); 
                }
                // Execute the query
                $stmt->execute();
                if($stmt->errno) {
                    displayMessage("Could not execute prepared statement", "red" );
                }
                // Download all the rows into a cache
                $result = $stmt->get_result( );

                // Get number of rows 
                $rowCount = $result->num_rows;

                if ($rowCount > 0) {
                    echo '<div class = "scrollableTable">';
                    echo "<table>\n";
                    // print headings
                    $heading = $result->fetch_assoc( );
                    echo "<tr>\n"; 
                    foreach($heading as $key=>$value){
                    echo "<th>" . $key . "</th>\n";
                    }
                    echo "</tr>\n";
                    
                    // Print first row to prevent it from being lost in the while loop
                    echo "<tr class = 'rows'>\n";
                    foreach($heading as $key=>$value){
                        if($key == 'Product Page'){
                            echo "<td><a href = '" . htmlentities($value) . "'>" . urldecode(htmlentities($value)) . "</a></td>";
                        }
                        else{
                            echo "<td>" . htmlentities($value) . "</td>\n";
                        }
                    }
                            
                    // Print rest of the rows
                    while($row = $result->fetch_assoc()) {
                        echo "<tr class = 'rows'>\n";
                        foreach($row as $key=>$value) {
                            if($key == 'Product Page'){
                                echo "<td><a href = '" . htmlentities($value) . "'>" . urldecode(htmlentities($value)) . "</a></td>";
                            }
                            else{
                                echo "<td>" . htmlentities($value) . "</td>\n";
                            }
                        }
                        echo "</tr>\n";
                    }
                    echo "</table></div>\n";
                    
                } else {
                    echo "No results from query";
                }
                // Free results
                $stmt->free_result( );

                // Close the statement
                $stmt->close( );
                }
            }
            else{

            // NO ITEM SELECTED
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo '<div class = "scrollableTable">';
                echo "<table>\n";
                // print headings
                $heading = $result->fetch_assoc( );
                echo "<tr>\n"; 
                foreach($heading as $key=>$value){
                echo "<th>" . $key . "</th>\n";
                }
                echo "</tr>\n";
                
                // Print first row to prevent it from being lost in the while loop
                echo "<tr class = 'rows'>\n";
                foreach($heading as $key=>$value){
                    if($key == 'Product Page'){
                        echo "<td><a href = '" . htmlentities($value) . "'>" . urldecode(htmlentities($value)) . "</a></td>";
                    }
                    else{
                        echo "<td>" . htmlentities($value) . "</td>\n";
                    }
                }
                        
                // Print rest of the rows
                while($row = $result->fetch_assoc()) {
                    echo "<tr class = 'rows'>\n";
                    foreach($row as $key=>$value) {
                        if($key == 'Product Page'){
                            echo "<td><a href = '" . htmlentities($value) . "'>" . urldecode(htmlentities($value)) . "</a></td>";
                        }
                        else{
                            echo "<td>" . htmlentities($value) . "</td>\n";
                        }
                    }
                    echo "</tr>\n";
                }
                echo "</table></div>\n";
                
            } else {
                echo "No results from query";
            }
        }
    }
    ?>

</head>
<body>
    <div id = "card">
        <!-- NAVIGATION BUTTONS FORM -->
        <form name = "frmDBF" action = "<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method = "POST">
            <div id="top-buttons">
                <button type = "submit" class = <?php if($selectedBtn == PRODUCT){echo "'selected'";}else{echo "'default'";}?> name = "btnArray" value = "product">Products</button>
                <button type = "submit" class = <?php if($selectedBtn == DEPARTMENT){echo "'selected'";}else{echo "'default'";}?> name = "btnArray" value = "department">Departments</button>
                <button type = "submit" class = <?php if($selectedBtn == STYLE){echo "'selected'";}else{echo "'default'";}?> name = "btnArray" value = "style">Styles</button>

                <!-- Hidden field to tell if user has visted the page before -->
                <input type = "hidden" name = "hidIsReturning" value="true">
            </div>
        </form>
        
        <?php
        DisplayTable();
        ?>
        
        <!-- EDIT FORM -->
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST" name="frmEdit" id="frmEdit">
            <div id = "selectBox">
                <label for="lstProduct">Select Product</label>
                <select name="lstProduct" id="lstProduct" onChange="this.form.submit();">
                    <option value="new">Select a product</option>
                    <?PHP
                    
                    // Loops throught the product table to populate the options
                    $sql = "SELECT product_id, productName AS 'name' 
                            FROM product 
                            ORDER BY 'name'";
                    $result = $conn->query($sql);
                    
                    while($row = $result->fetch_assoc()) {  
                        echo "<option value='" . $row['product_id'] . "'>" . $row['name'] . "</option>\n";
                    }
                    ?>
                </select> 
                <a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">Clear</a>
            </div>

            <!-- PRODUCT INFORMATION FIELDSET -->
            <fieldset>
                <legend>Product Information</legend>
                    
                    <!-- TEXT FIELDS | NAME, QTY, PRICE, PAGE -->
                    <div class = "gridBox" id = "textFields">
                        <label for="txtName">Name</label>
                        <input type="text" name="txtName"   id="txtName"   value="<?php echo $thisProduct['Name']; ?>" /><br/>
                        
                        <label for="txtQty">Qty</label>
                        <input type="text" name="txtQty"   id="txtQty"   value="<?php echo $thisProduct['Qty']; ?>" /><br/>
                    
                        <label for="txtPrice">Price</label>
                        <input type="text" name="txtPrice"   id="txtPrice"   value="<?php echo $thisProduct['Price']; ?>" /><br/>
                    
                        <label for="txtPage">Product Page</label>
                        <input type="text" name="txtPage"   id="txtPage"   value="<?php echo urldecode($thisProduct['Page']); ?>" />
                    </div>
                    
                    <!-- RADIO BUTTONS | DEPARTMENT/STYLE -->
                    <div class = "gridBox" id = "departments">
                        <p>Department</p>
                        <hr>
                        <label for="optAccessories">Accessories</label>
                        <input type="radio" name="optDepartment" id="optAccessories" value= 3 <?php echo ($thisProduct['Department']== "Accessories" ? 'checked' : '');?>/><br/>
                        <label for="optGuitar">Guitar</label>
                        <input type="radio" name="optDepartment" id="optGuitar" value= 1 <?php echo ($thisProduct['Department']== "Guitar" ? 'checked' : '');?>/><br/>
                        <label for="optPiano">Piano</label>
                        <input type="radio" name="optDepartment" id="optPiano" value= 2 <?php echo ($thisProduct['Department']== "Piano" ? 'checked' : '');?>/>
                    </div>              
                    <div class = "gridBox" id = "styles">
                        <p>Style</p>
                        <hr>
                        <label for="optBlack">Black</label>
                        <input type="radio" name="optStyle" id="optBlack" value= 1 <?php echo ($thisProduct['Style']== "Black" ? 'checked' : '');?>/><br/>
                        <label for="optWhite">White</label>
                        <input type="radio" name="optStyle" id="optWhite" value= 2 <?php echo ($thisProduct['Style']== "White" ? 'checked' : '');?>/><br/>
                        <label for="optWooden">Wooden</label>
                        <input type="radio" name="optStyle" id="optWooden" value= 3 <?php echo ($thisProduct['Style']== "Wooden" ? 'checked' : '');?>/>
                    </div>
                    
                    <!-- CHECK BOX | ON SALE -->
                    <div class = "gridBox" id = "onSale">
                        <label for="chkOnSale">On Sale</label>
                        <input type="checkbox" id="chkOnSale" name="chkOnSale" value= 1 <?php echo ($thisProduct['onSale']==1 ? 'checked' : '');?>>
                    </div> 
            </fieldset>
            
            <!-- BOTTOM BUTTONS | DELETE, ADD, UPDATE -->
            <br />
            <div id = "bottom-buttons">
                <button name="btnSubmit" value="delete" onclick="this.form.submit();">Delete</button>
                        
                <button name="btnSubmit" value="new" onclick="this.form.submit();">Add</button>
                        
                <button name="btnSubmit" value="update" onclick="this.form.submit();">Update</button>
            </div>     
            <!-- Hidden field to notify return visitor -->
            <input type="hidden" name="hidIsReturning" value="true" />
        </form>

        <script>
            document.getElementById("lstProduct").value = "<?PHP echo $thisProduct['product_id']; ?>";
        </script>    
    </div>
    <div id = "bottomlinks">
    <a id = "links" href="readMe.html">ReadMe</a>
    <a id = "links"href="reflection.html">Reflection</a>
    </div>
</body>
</html>