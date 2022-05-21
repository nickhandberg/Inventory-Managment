<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset="utf-8" />
<title>Create Database</title>
<link rel="stylesheet" type="text/css" href="style.css">

<?PHP
/* createDatabase.php - Create and populate a database
   Written by Nicholas Handberg
   Written:   11/18/2021
   Revised:   
*/
   
// Connection constants  
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

/***************************************************************************************************************************
    CREATE DATABASE
    Description: Drops and creates the database and tables specified
    Parameters: none
****************************************************************************************************************************/
function createDatabase(){
    global $conn;

    // Drops the database
    $sql = "DROP DATABASE " . DATABASE_NAME;
    runQuery($sql, "Dropping database: " . DATABASE_NAME, false);

    // Creates the database if it doesnt exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DATABASE_NAME;
    runQuery($sql, "Creating database: " . DATABASE_NAME, true);

    // Selects the created database
    $conn -> select_db(DATABASE_NAME);


    /************************************
                CREATE TABLES
    ************************************/
    // Create Table: product
    $sql = "CREATE TABLE IF NOT EXISTS product (
        product_id          INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        productName         VARCHAR(25) NOT NULL,
        productQty          INT(4) NOT NULL,
        productPrice        FLOAT(4.2)  NOT NULL,
        productPage         VARCHAR(150),
        onSale              BOOLEAN,
        department_id       INT(6) NOT NULL
        )";
    runQuery($sql, "Creating product table", true);


    // Create Table: department
    $sql = "CREATE TABLE IF NOT EXISTS department (
        department_id   INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        departmentName  VARCHAR(50) NOT NULL
        )";
    runQuery($sql, "Creating department table", true);

    // Create Table: style
    $sql = "CREATE TABLE IF NOT EXISTS style (
        style_id     INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        style        VARCHAR(50) NOT NULL
        )";
    runQuery($sql, "Creating style table", true);

    // Create Table: productStyle
    $sql = "CREATE TABLE IF NOT EXISTS productStyle (
        product_id        INT(6) NOT NULL,
        style_id          INT(6) NOT NULL
        )";
    runQuery($sql, "Creating productStyle table", true);
} // end of createDatabase()



/***************************************************************************************************************************
    POPULATE TABLE
    Description: Populates the tables of the database using hard-coded array data
    Parameters: none
****************************************************************************************************************************/
function populateTable(){

    // Populate Table: product
    $productArray = array(
        array("Fender Stratocaster", 11, 999.99, "test", 1, 1),
        array("Tuner", 52, 15.99, "test", 0, 3),
        array("Piano", 23, 599.99, "test", 1, 2),
        array("Picks 12pk", 837, 3.99, "test", 0, 3)
        );

    foreach($productArray as $product) {   
        $sql = "INSERT INTO product (productName, productQty, productPrice, productPage, onSale, department_id) 
                VALUES ('" . $product[0] . "', 
                        '" . $product[1] . "', 
                        '" . $product[2] . "', 
                        '" . $product[3] . "',
                        '" . $product[4] . "',
                        '" . $product[5] . "')";
        runQuery($sql, "Populating product table", true);
    }

    // Populate Table: department
    $departmentArray = array("Guitar", "Piano", "Accessories");

    foreach($departmentArray as $department) {   
        $sql = "INSERT INTO department (departmentName) 
                VALUES ('" . $department . "')";
        runQuery($sql, "Populating department table", true);
    }

    // Populate Table: style
    $styleArray = array("Black", "White", "Wooden");

    foreach($styleArray as $style) {   
        $sql = "INSERT INTO style (style) 
                VALUES ('" . $style . "')";
        runQuery($sql, "Populating style table", true);
    }

    // Populate Table: productStyle
    $productStyleArray = array(
        array(1,3),
        array(2,2),
        array(3,1),
        array(4,1));

    foreach($productStyleArray as $productStyle) {   
        $sql = "INSERT INTO productStyle (product_id, style_id) 
                VALUES ('" . $productStyle[0] . "',
                        '" . $productStyle[1] . "')";
        runQuery($sql, "Populating productStyle table", true);
    }
}


/***************************************************************************************************************************
    RUN QUERY
    Description: Runs the query and displays a message telling the user if it was successful or had an error
    Parameters: $sql: statement to run, $msg: message to display, $showSuccess: boolean to determine to show success messages
****************************************************************************************************************************/
function runQuery($sql, $msg, $showSuccess) {
    global $conn;
    
   // runs the query
   if ($conn->query($sql) === TRUE) {
      if($showSuccess) {
         echo "<p id = 'create'>".$msg . " was successful</p><br />";
      }
   } else {
      echo "Error: " . $msg . " using SQL: " . $sql . "<br />" . $conn->error;
   }  
}
?>
</head>

<body>
    
    <?php
        echo "<h1>Create Database/Tables</h1>";
        echo '<pre>';
        createDatabase();
        echo "<h1>Populate Tables</h1>";
        populateTable();
        echo '</pre>';
        $conn->close();
    ?>
</body>
</html>