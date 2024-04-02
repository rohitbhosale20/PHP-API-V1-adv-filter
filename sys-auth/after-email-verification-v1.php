<?php
header("Access-Control-Allow-Origin: *");
header("Cross-Origin-Opener-Policy: same-origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$dbname = "Data_Gateway";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST["email"]); 
    $firstName = $conn->real_escape_string($_POST["firstName"]);
    $lastName = $conn->real_escape_string($_POST["lastName"]);
    $companyName = $conn->real_escape_string($_POST["companyName"]);
    $password = $conn->real_escape_string($_POST["password"]);

    if (empty($email) || empty($firstName) || empty($lastName) || empty($companyName) || empty($password)) {
        echo "Error: All form fields are required.";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $checkEmailQuery = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        $deleteExistingQuery = "DELETE FROM user WHERE email = '$email'";
        $conn->query($deleteExistingQuery);
    }

    $insertUserQuery = "INSERT INTO user (email, firstName, lastName, companyName, password) VALUES ('$email', '$firstName', '$lastName', '$companyName', '$hashedPassword')";

    if ($conn->query($insertUserQuery) === TRUE) {
        $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $email);
        $createTableSql = "CREATE TABLE IF NOT EXISTS $tableName (
            id int(11) NOT NULL,
            user_id varchar(255) DEFAULT NULL,
            pid varchar(18) DEFAULT NULL,
            First_Name varchar(500) DEFAULT NULL,
            last_name varchar(255) DEFAULT NULL,
            email_address varchar(255) DEFAULT NULL,
            company_name varchar(255) DEFAULT NULL,
            company_domain varchar(255) DEFAULT NULL,
            job_title varchar(255) DEFAULT NULL,
            job_function varchar(255) DEFAULT NULL,
            job_level varchar(255) DEFAULT NULL,
            Company_Address varchar(500) DEFAULT NULL,
            city varchar(255) DEFAULT NULL,
            State varchar(500) DEFAULT NULL,
            Zip_Code varchar(500) DEFAULT NULL,
            country varchar(255) DEFAULT NULL,
            Telephone_Number varchar(500) DEFAULT NULL,
            Employee_Size varchar(500) DEFAULT NULL,
            Industry varchar(500) DEFAULT NULL,
            Company_Link varchar(2000) DEFAULT NULL,
            Prospect_Link varchar(2000) DEFAULT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (pid)
        )";

        if ($conn->query($createTableSql) === TRUE) {
           
        } else {
           
        }
    } else {
        echo "Error: " . $insertUserQuery . "<br>" . $conn->error;
    }

   
    header("Location: https://app.datagateway.in");
    exit();
}

$conn->close();
?>




<html>
   <head>
      <link
         href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css"
         rel="stylesheet"
         />
      <link
         href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap"
         rel="stylesheet"
         />
      <meta
         name="viewport"
         content="width=device-width,initial-scale=1,maximum-scale=1"
         />
      <style>
         body {
         font-family: "Inter", sans-serif;
         }
      </style>
      <script
         src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js"
         defer
         ></script>
   </head>
   <body class="min-h-screen bg-gray-100 text-gray-900 flex justify-center">
      <div
         class="max-w-screen-xl m-0 sm:m-20 bg-white shadow sm:rounded-lg flex justify-center flex-1"
         >

         <div class="lg:w-1/2 xl:w-5/12 p-6 sm:p-12">
            <div>
    <img src="https://www.datagateway.in/assets/data-gateway.png" class="w-24 mx-auto" />
</div>
<form id="signupForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div  class="mt-4 flex flex-col items-center">
 <h3 class="text-xs xl:text-sm font-extrabold mt-1 mb-1"> <!-- Adjusted text size and margin here -->
        Sign up
    </h3>       <div class="w-full flex-1 mt-8">
                  <div class="flex flex-col items-center">
                  </div>
                  <div class="mx-auto max-w-xs">
                     <input
                        class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                        type="email" id="email" name="email" placeholder="Email" value="<?php echo $_GET['email']; ?>" readonly/>
                     <input
                        class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                        type="text" id="firstName" name="firstName" placeholder="First Name" />
                     <input
                        class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                        type="text" id="lastName" name="lastName" placeholder="Last Name" required/>
                     <input
                        class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                        type="text" placeholder="Company Name" id="companyName" name="companyName" required/>
                     <input
                        class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5"
                        type="password"
                        placeholder="Password" id="password" name="password" required/>
                     <button type="submit" 
    class="mt-5 tracking-wide font-semibold bg-yellow-500 text-gray-100 w-full py-4 rounded-lg hover:bg-yellow-700 transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none">
    <svg
        class="w-6 h-6 -ml-2"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        >
        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
        <circle cx="8.5" cy="7" r="4" />
        path d="M20 8v6M23 11h-6" />
    </svg>
    <span class="ml-3">
    Sign Up
    </span>
</button>
</form>

                     <p class="mt-6 text-xs text-gray-600 text-center">
                        I agree to abide by datagateway's
                        <a href="#" class="border-b border-gray-500 border-dotted">
                        Terms of Service
                        </a>
                        and its
                        <a href="#" class="border-b border-gray-500 border-dotted">
                        Privacy Policy
                        </a>
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div class="flex-1 bg-indigo-100 text-center hidden lg:flex">
            <div
               class="m-12 xl:m-16 w-full bg-contain bg-center bg-no-repeat"
               style="background-image: url('https://netlify.apollo.io/_next/image?url=https%3A%2F%2Fnetlify.apollo.io%2F_next%2Fstatic%2Fmedia%2Fpeople-cards.ccb41286.png&w=1920&q=75');"
               ></div>
         </div>
      </div>
   </body>
</html>
