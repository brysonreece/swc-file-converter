<!--

ABANDON ALL HOPE YE WHO ENTER

-->


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<meta name="description" content="SWC File Converter">
		<meta name="author" content="Bryson Reece">

		<title>SWC File Converter</title>

		<!-- Bootstrap core CSS -->
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

		<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
		<link href="https://maxcdn.bootstrapcdn.com/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

		<!-- Custom styles for this template -->
		<link href="http://getbootstrap.com/examples/starter-template/starter-template.css" rel="stylesheet">

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>

	<!--
	=========================
		UPLOAD HANDLER
	=========================
	-->
	<?php

		$error = "";

		if (isset($_POST['submit'])) {
			// Directory to temporarily store uploaded files
			$uploadDir = "/mnt/c/Users/bryso/Desktop/";
			// Directory to temporarily store converted files
			$tmpDir = "/mnt/c/Users/bryso/Desktop/";

			// uFileExtension
			$uploadedFileType = pathinfo($_FILES["userFile"]["name"], PATHINFO_EXTENSION);
			// uFilename
			$uploadedFilename = basename($_FILES["userFile"]["name"], '.' . $uploadedFileType);
			// uploadDir/uFilename.uFileExtension
			$uploadedFilePath = $uploadDir . $uploadedFilename . '.' . $uploadedFileType;

			// cFileExtension
			$convertedFileType = "";

			$outputs = array(
				"markdown" => "md",
				"pdf" => "pdf",
				"rst" => "rst",
				"html" => "html",
				"mediawiki" => "txt",
				"textile" => "textile",
				"org" => "org",
				"texi" => "texi",
				"docbook" => "dbk",
				"docx" => "docx",
				"epub" => "epub",
				"mobi" => "mobi",
				"asciidoc" => "txt",
				"rtf" => "rtf"
			);

			// Start by checking if format selections were made
			if (!isset($_POST["inputButton"])) {
				$error = "An input format selection is required.";
			}
			elseif (!isset($_POST["outputButton"])) {
				$error = "An output format selection is required.";
			}
			// If they were, check if the user's selected value is in $outputs
			else {
				// If it is, set our converted file's filetype
				if (array_key_exists($_POST["outputButton"], $outputs)) {
					$convertedFileType = $outputs[$_POST["outputButton"]];
				}
				// If it's not, throw an error
				else {
					$error = "Cannot get converted file extension.";
				}
			}

			// Check if a file was even uploaded
			if (!file_exists($_FILES["userFile"]["tmp_name"]) || !is_uploaded_file($_FILES["userFile"]["tmp_name"])) {
				$error = "No file was selected to upload.";
			}
			// If it was, make sure it's not too large
			elseif ($_FILES["userFile"]["size"] > 40000000) {
				$error = "Sorry, your file is too large. (Max file size is 40MB)";
			}

			// cFilename
			$convertedFilename = $uploadedFilename;
			// tmpDir/cFilename.cFileExtension
			$convertedFilePath = $tmpDir . $convertedFilename . '.' . $convertedFileType;

			// Check if $uploadOk is set to 0 by an error
			if ($error == "") {

				if (move_uploaded_file($_FILES["userFile"]["tmp_name"], $uploadedFilePath)) {

					//set POST variables
					$url = 'http://c.docverter.com/convert';

					$fields = array(
						'from' => $_POST["inputButton"],
						'to' => $_POST["outputButton"],
						// TODO: Add option to select different charsets
						'input_files[]' => "@/" . $uploadedFilePath . ";type=" . mime_content_type($uploadedFilePath) . "; charset=UTF-8"
						// TODO: Add option to select or upload CSS stylesheets
						// TODO: Add option to make HTML standalone
					);

					//open connection
					$ch = curl_init();

					//set options
					curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //needed so that the $result=curl_exec() output is the file and isn't just true/false

					//execute POST
					$result = curl_exec($ch);

					//close connection
					curl_close($ch);

					//write to file
					$convertedFile = fopen($convertedFilePath, 'w');
					fwrite($convertedFile, $result);
					fclose($convertedFile);

					// Construct the file headers
					header('Content-Type: ' . mime_content_type($convertedFilePath), true, 200);
					header('Content-Disposition: attachment; filename="' . $convertedFilename . '.' . $convertedFileType . '"');
					header("Content-Transfer-Encoding: binary");
					header('Accept-Ranges: bytes');
					header('Pragma: no-cache');
					ob_clean();
					flush();
					// Force the file to download in the user's browser
					readfile($convertedFilePath);

					// Delete the file from our server space
					unlink($convertedFilePath);
					unlink($uploadedFilePath);

					exit();

				}
				else {
					$error = "Sorry, there was an error uploading your file. </br>";
				}
			}
		}
	?>

	<body>

		<div class="container">
			<div class="starter-template">
				<?php
					if ($error == "") {
						echo "<h1>SWC File Converter</h1>";
						echo "<p class=\"lead\">Use this page as a way to quickly convert a variety of document types</p>";
					}
					else {
						echo "<h2>" . $error . "</h2></br><h3>Please try again.</h3>";
					}
				?>
			</div>
		</div><!-- /.container -->

		<form action="" method="post" enctype="multipart/form-data">
			<div class="container-fluid">
				<div class="row">
					<!-- Input Formats -->
					<div class="col-lg-1"></div>
					<div class="col-lg-4">
						<div align="center"><h2><u>Input Format</u></h2></div>
						<fieldset id="inputButton">
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" value="markdown" name="inputButton">Markdown
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" value="textile" name="inputButton">Textile
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" value="rst" name="inputButton">reStructured Text
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" value="html" name="inputButton">HTML
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" value="docbook" name="inputButton">Docbook
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" value="latex" name="inputButton">LaTeX
								</label>
							</div>
						</fieldset>
					</div>
					<div class="col-lg-2"></div>
					<!-- Output Format -->
					<div class="col-lg-4">
						<div align="center"><h2><u>Output Format</u></h2></div>
						<div class="row">
							<fieldset id="outputButton">
								<div class="col-lg-6">
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="markdown" name="outputButton">Markdown
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="pdf" name="outputButton">PDF
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="rst" name="outputButton">reStructuredText
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="html" name="outputButton">HTML
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="mediawiki" name="outputButton">MediaWiki Markup
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="textile" name="outputButton">Textile
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="org" name="outputButton">Emacs Org-Mode
										</label>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="texinfo" name="outputButton">GNU Texinfo
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="docbook" name="outputButton">DocBook XML
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="docx" name="outputButton">Word docx
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="epub" name="outputButton">EPUB
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="mobi" name="outputButton">MOBI
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="asciidoc" name="outputButton">AsciiDoc
										</label>
									</div>
									<div class="radio">
										<label class="radio-inline">
											<input type="radio" value="rtf" name="outputButton">Rich Text Format
										</label>
									</div>
								</div>
							</fieldset>
						</div>
						<div class="col-lg-1"></div>
					</div>
				</div>

			</br>
			</br>

			<div align="center">
				<input style="display: inline;white-space:nowrap;" type="file" name="userFile" id="userFile">
				<input style="display: inline;white-space:nowrap;" type="submit" value="Convert!" name="submit">
			</div>

		</form>


		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="https:\/\/code.jquery.com\/jquery-3.1.1.min.js"><\/script>')</script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
		<script src="https://maxcdn.bootstrapcdn.com/js/ie10-viewport-bug-workaround.js"></script>
	</body>
</html>
