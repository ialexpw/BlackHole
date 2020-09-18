<?php
	/**
	 * index.php
	 *
	 * Main page for receiving and managing the requests
	 *
	 * @package    BlackHole
	 * @author     Alex White
	 * @copyright  2020 BlackHole
	 * @link       https://m0x.org
	 */

	// Generate a token
	$genToken = substr(str_shuffle(md5(microtime())), 0, 16);

	// API area
	if(isset($_GET['api'])) {
		// Create a bin
		if(isset($_GET['create'])) {
			// Save the url
			$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . '?rq=' . $genToken . '&inspect';

			// Arrange the array
			$jsArr = array(
				"id" => $genToken,
				"link" => $url
			);

			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($jsArr);
			exit();
		}

		// Get details on a bin
		if(isset($_GET['details']) && !empty($_GET['details'])) {
			// Validate the id
			if(strlen($_GET['details']) == 16 && ctype_alnum($_GET['details'])) {
				// Check the file is still available
				if(file_exists($_GET['details'] . '.txt')) {
					// Viewing the raw details
					if(isset($_GET['raw'])) {
						echo file_get_contents($_GET['details'] . '.txt');
						exit();
					}
				}
			}
		}
	}

	// Check for a request
	if(isset($_GET['rq']) && strlen($_GET['rq']) == 16 && ctype_alnum($_GET['rq'])) {
		header('Access-Control-Allow-Origin: *');

		// Check if the log exists
		$query = $_GET['rq'];

		// Name the file after the Url
		$file = $query . ".txt";

		// Create the file (if it does not exist)
		if(!file_exists($file)) {
			$fop = fopen($file, 'w');

			// Check that the file can be opened, otherwise return error
			if($fop) {
				// Close the file
				fclose($fop);
			}else{
				// Needs write access
				exit(http_response_code(500));
			}
		}

		// Get the current contents of the file
		$current = file_get_contents($file);

		// Show the url
		$reqUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$reqUrl = str_replace("&inspect", "", $reqUrl);

		$urlTpl = '<div class="row">';
		$urlTpl .= '<div class="col-md-11">';
		$urlTpl .= '<div class="input-group mb-3">';
		$urlTpl .= '<div class="input-group-prepend">';
		$urlTpl .= '<span class="input-group-text" id="inputGroup-sizing-default">Request URL</span>';
		$urlTpl .= '</div>';
		$urlTpl .= '<input type="text" class="form-control" onFocus="this.select();" value="' . $reqUrl . '" style="background-color: #fff;" readonly>';
		$urlTpl .= '</div>';
		$urlTpl .= '</div>';

		// Button
		$urlTpl .= '<div class="col-md-1">';
		$urlTpl .= '<a class="btn btn-light" href="index.php?rq=' . $_GET['rq'] . '&clear" role="button">Clear</a>';
		$urlTpl .= '</div>';
		$urlTpl .= '</div>';

		// Are we inspecting the logs
		if(isset($_GET['inspect'])) {
			// No data yet
			if(empty($current)) {
				// No data, show the Url
				$getTpl = file_get_contents("req.tpl.html");
				$getTpl = str_replace("REQ_URL", $urlTpl, $getTpl);
				$getTpl = str_replace("CURRENT_HERE", "", $getTpl);
				echo $getTpl;
			}else{
				// There is data, load the content
				$getTpl = file_get_contents("req.tpl.html");
				$getTpl = str_replace("REQ_URL", $urlTpl, $getTpl);
				$getTpl = str_replace("CURRENT_HERE", $current, $getTpl);
				echo $getTpl;
			}
		// Clearing the file
		}else if(isset($_GET['clear'])) {
			// Clear file contents
			if(file_exists($file)) {
				file_put_contents($file, "");

				// Redirect back
				header("Location: ?rq=" . $_GET['rq'] . "&inspect");
			}
		// Requesting
		}else{
			$postdata = file_get_contents("php://input");

			// Header
			$newlog = '<br /><div class="card"><h5 class="card-header">';
			$newlog .= $_SERVER["REQUEST_METHOD"] . ' ' . $_SERVER['REQUEST_URI'] . '<span class="float-right">' . date('Y-m-d H:i:s') . '</span>';
			$newlog .= '</h5>';

			// Body
			$newlog .= '<div class="card-body">';
			$newlog .= '<div class="row">';
			$newlog .= '<div class="col-md-6">';
    
			//$newlog .= "IP: ".$_SERVER['REMOTE_ADDR'] . "<br />";

			// Headers
			$newlog .= '<h5>Headers</h5>';
			foreach (getallheaders() as $name => $value) {
				$newlog .= "<b>$name</b>: $value<br />";
			}
			$newlog .= '</div>';

			// Body
			$newlog .= '<div class="col-md-6">';
			$newlog .= '<h5>Body</h5>';
			$newlog .= "<pre>" . htmlspecialchars($postdata) . "</pre>";
			$newlog .= "</div></div>";
			$newlog .= "</div></div>";
			$current = $newlog . $current;
			$current = keepLines($current, 700);

			// Put the request into the file
			file_put_contents($file, $current);

			// Return a 200
			exit(http_response_code(200));
		}
	}else{
		// Show the generate button if there is no current url
		$genLink = '<br /><a class="btn btn-info" href="index.php?rq=' . $genToken . '&inspect" role="button">Generate Url</a>';

		$getTpl = file_get_contents("req.tpl.html");
		$getTpl = str_replace("REQ_URL", "", $getTpl);
		$getTpl = str_replace("CURRENT_HERE", $genLink, $getTpl);
		echo $getTpl;
	}

	function keepLines($str, $num=10) {
		$lines = explode("\n", $str);
		$firsts = array_slice($lines, 0, $num);
		return implode("\n", $firsts);
	}
?>