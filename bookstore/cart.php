<?php
// the shopping cart needs sessions, to start one
/*Array of session(
			cart => array (
				book_isbn (get from $_POST['book_isbn']) => number of books
			),
			items => 0,
			total_price => '0.00')*/
session_start();
require_once "./functions/database_functions.php";
require_once "./functions/cart_functions.php";

// book_isbn got from form post method, book.php change this place later.
if (isset($_POST['bookisbn'])) {
	$book_isbn = $_POST['bookisbn'];
}

if (isset($book_isbn)) {
	// new item selected
	if (!isset($_SESSION['cart'])) {
		// $_SESSION['cart'] is associative array => quantity
		$_SESSION['cart'] = array();

		$_SESSION['total_items'] = 0;
		$_SESSION['total_price'] = '0.00';
	}

	if (!isset($_SESSION['cart'][$book_isbn])) {
		$_SESSION['cart'][$book_isbn] = 1;
	} elseif (isset($_POST['cart'])) {
		$_SESSION['cart'][$book_isbn]++;
		unset($_POST);
	}
}

// if save change button is clicked , change the quantity of each bookisbn
if (isset($_POST['save_change'])) {
	foreach ($_SESSION['cart'] as $isbn => $qty) {
		if ($_POST[$isbn] == '0') {
			unset($_SESSION['cart']["$isbn"]);
		} else {
			$_SESSION['cart']["$isbn"] = $_POST["$isbn"];
		}
	}
}

// print out header here
$title = "Your shopping cart";
require "./template/header.php";

if (isset($_SESSION['cart']) && (array_count_values($_SESSION['cart']))) {
	$_SESSION['total_price'] = total_price($_SESSION['cart']);
	$_SESSION['total_items'] = total_items($_SESSION['cart']);
?>
	<form action="cart.php" method="post">
		<table class="table">
			<tr>
				<th>Item</th>
				<th>Price</th>
				<th>Quantity</th>
				<th>Total price</th>
			</tr>
			<?php
			foreach ($_SESSION['cart'] as $isbn => $qty) {
				$conn = db_connect();
				$book = mysqli_fetch_assoc(getBookByIsbn($conn, $isbn));
			?>
				<tr>
					<td><?php echo $book['book_title'] . " by " . $book['book_author']; ?></td>
					<td><?php echo "ብር" . $book['book_price']; ?></td>
					<td><input type="text" value="<?php echo $qty; ?>" size="4" name="<?php echo $isbn; ?>"></td>
					<td><?php echo "ብር" . $qty * $book['book_price']; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th><?php echo $_SESSION['total_items']; ?></th>
				<th><?php echo "ብር" . $_SESSION['total_price']; ?></th>
			</tr>
		</table>
		<input type="submit" class="btn btn-primary" name="save_change" value="save change">
	</form>
	<br /><br />
	<a href="checkout.php" class="btn btn-primary">check</a>
	<a href="books.php" class="btn btn-primary">continue shopping</a>
<?php
} else {
	echo "<p class=\"text-warning\">Your cart is empty! Please make sure you add some books in it!</p>";
}
if (isset($conn)) {
	mysqli_close($conn);
}
require_once "./template/footer.php";
?>