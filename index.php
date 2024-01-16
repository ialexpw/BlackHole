<?php
	/**
	 * index.php
	 *
	 * Main page for receiving and managing the requests
	 *
	 * @package    BlackHole
	 * @author     Alex White
	 * @copyright  2024 BlackHole
	 * @link       https://github.com/ialexpw/BlackHole
	 */

	// Set the log output folder
	$output_folder = 'log/';

	// Generate a token
	$genToken = substr(str_shuffle(md5(microtime())), 0, 16);

	// Make the $output_folder, if it doesn't exist already
	if (!file_exists($output_folder)) {
		mkdir($output_folder, 0777, true);
	}

	// Export data
	if(isset($_GET['export']) && !empty($_GET['export'])) {
		// Validate the id
		if(strlen($_GET['export']) == 16 && ctype_alnum($_GET['export'])) {
			// Check the file is still available
			if(file_exists($output_folder . $_GET['export'] . '.txt')) {
				// Store filename
				$fnme = $output_folder . $_GET['export'] . '.txt';

				// Contents of file (without tags)
				$fcont = strip_tags(file_get_contents($output_folder . $_GET['export'] . '.txt'));

				// Download
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($fnme));
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				echo $fcont;
				exit;
			}
		}
	}

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
				if(file_exists($output_folder . $_GET['details'] . '.txt')) {
					// Viewing the raw details
					if(isset($_GET['raw'])) {
						// Print the details (with tags)
						exit(htmlspecialchars(file_get_contents($output_folder . $_GET['details'] . '.txt')));
					}else{
						// Print the details (stripping tags)
						header("Content-Type: text/plain; charset=utf-8");
						exit(htmlspecialchars_decode(strip_tags(file_get_contents($output_folder . $_GET['details'] . '.txt'))));
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
		$file = $output_folder . $query . ".txt";

		$file_body = $output_folder . $query . "_body_" . (new DateTime())->format('Y-m-d-His.u') . ".txt";

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

		// Home Button
		$urlTpl .= '<div class="col-1">';
		$urlTpl .= '<a class="btn btn-outline-success" href="index.php" role="button">Home</a>';
		$urlTpl .= '</div>';

		// Request URL Button/Field section
		$urlTpl .= '<div class="col-5">';
		$urlTpl .= '<div class="input-group mb-3">';
		$urlTpl .= '<span class="input-group-text" id="reqUrl">Req. URL</span>';
		$urlTpl .= '<input type="text" class="form-control" onFocus="this.select();" value="' . $reqUrl . '" style="background-color: #fff;" aria-label="Request URL" readonly>';
		$urlTpl .= '</div>';
		$urlTpl .= '</div>';

		// Refresh Rate
		$urlTpl .= '<div class="col-2">';
		$urlTpl .= '<div class="input-group mb-3">';
		$urlTpl .= '<span class="input-group-text" id="autoRefresh"><i class="bi bi-arrow-clockwise"></i></span>';
		$urlTpl .= '<select class="form-control"  id="selectX" onchange="getSelectValue(\'selectX\');">';
		$urlTpl .= '<option>0 - Disable</option> <option>2 seconds</option> <option>10 seconds</option> <option>20 seconds</option> <option>60 seconds</option> <option>120 seconds</option>';
		$urlTpl .= '</select>';
		$urlTpl .= '</div>';
		$urlTpl .= '</div>';

		// View API
		$urlTpl .= '<div class="col-4">';
		$urlTpl .= '<a class="btn btn-outline-primary" href="index.php?api&details=' . $_GET['rq'] . '" role="button" target="_blank" title="View Results API">View Results API</a> ';

		// Export Data Button
		$urlTpl .= '<a class="btn btn-outline-primary" href="?export=' . $_GET['rq'] . '" role="button">Export Data</a> ';

		// Clear Log Button
		$urlTpl .= '<a class="btn btn-outline-danger" href="index.php?rq=' . $_GET['rq'] . '&clear" role="button">Clear Log</a>';
		$urlTpl .= '</div>';
		$urlTpl .= '</div>';

		// Page load time - footer
		$footer = '<p>Powered by <a href="https://github.com/ialexpw/BlackHole">BlackHole</a> - <strong>Page Loaded: </strong>' . date('Y-m-d H:i:s') . '</p>';

		// Are we inspecting the logs
		if(isset($_GET['inspect'])) {
			// No data yet
			if(empty($current)) {
				// No data, show the URL
				$getTpl = file_get_contents("req.tpl.html");
				$getTpl = str_replace("REQ_URL", $urlTpl, $getTpl);
				$getTpl = str_replace("CURRENT_HERE", "<br />No data available yet.", $getTpl);
				$getTpl = str_replace("TEMPLATE_TITLE_CONTENT", "Request: " . $_GET['rq'], $getTpl);
				$getTpl = str_replace("FOOTER", $footer, $getTpl);
				echo $getTpl;
			}else{
				// There is data, load the content
				$getTpl = file_get_contents("req.tpl.html");
				$getTpl = str_replace("REQ_URL", $urlTpl, $getTpl);
				$getTpl = str_replace("CURRENT_HERE", $current, $getTpl);
				$getTpl = str_replace("TEMPLATE_TITLE_CONTENT", "Request: " . $_GET['rq'], $getTpl);
				$getTpl = str_replace("FOOTER", $footer, $getTpl);
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
			$nl = "\n";

			// Header
			$newlog = '<br /><div class="card"><h5 class="card-header">';
			$newlog .= $_SERVER["REQUEST_METHOD"] . ' ' . $_SERVER['REQUEST_URI'] . "\n" . ' <span>(' . date('Y-m-d H:i:s') . ')</span>';
			$newlog .= '</h5>' . $nl;

			// Body
			$newlog .= '<div class="card-body">';
			$newlog .= '<div class="row">';
			$newlog .= '<div class="col-md-6">';

			// Headers
			$newlog .= '<h5>Headers</h5>' . $nl;
			foreach (getallheaders() as $name => $value) {
				$newlog .= "<b>$name</b>: $value<br />" . $nl;
			}
			$newlog .= '</div>';

			// Body
			$newlog .= '<div class="col-md-6">';
			$newlog .= '<h5>Body</h5>' . $nl;

			// Check for empty body
			if(empty($postdata)) {
				$newlog .= "Empty body";
			}else{
				$newlog .= "<pre>" . htmlspecialchars($postdata) . "</pre>" . $nl . $nl;
			}

			$newlog .= "</div></div>";
			$newlog .= "</div></div>";
			$current = $newlog . $current;
			$current = keepLines($current, 100000);

			// Put the request into the file
			file_put_contents($file, $current);

			// Write body content into separate log file
			if ($postdata) {
				file_put_contents($file_body, $postdata);
			}

			// Return a 200
			exit(http_response_code(200));
		}
	}else{
		// Generate Home Page

		// Show the generate button if there is no current url
		$genLink = '<br /><a class="btn btn-outline-primary" href="index.php?rq=' . $genToken . '&inspect" role="button">Generate URL</a>';

		$getTpl = file_get_contents("req.tpl.html");
		$getTpl = str_replace("REQ_URL", "", $getTpl);
		$getTpl = str_replace("CURRENT_HERE", $genLink, $getTpl);
		$getTpl = str_replace("TEMPLATE_TITLE_CONTENT", "Home", $getTpl);
		$getTpl = str_replace("FOOTER", "", $getTpl);
		echo $getTpl;
	}

	function keepLines($str, $num=10) {
		$lines = explode("\n", $str);
		$firsts = array_slice($lines, 0, $num);
		return implode("\n", $firsts);
	}
?>