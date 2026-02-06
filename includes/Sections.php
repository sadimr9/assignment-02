<?php

class Header{
    public function header($page){
        echo '
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>'.$page.' | Profile Management | Sadi Chowdhury</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        ';
    }
    
}


class Sidebar{
    public function sidebar($page){
        echo ' <aside class="lg:w-64 bg-white shadow-lg">
        <div class="p-6 border-b">
          <h1 class="font-bold text-lg bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Profile Management</h1>
          <p class="text-xs text-gray-500">'.$page.'</p>
        </div>
        <nav class="p-4 space-y-2">
          <a href="dashboard.php" class="block p-3 rounded-lg bg-indigo-50 text-indigo-700 font-medium">My Profile</a>
          <a href="edit-profile.php" class="block p-3 rounded-lg hover:bg-gray-50 font-medium text-gray-700">Edit Profile</a>
          <a href="change-password.php" class="block p-3 rounded-lg hover:bg-gray-50 font-medium text-gray-700">Change Password</a>
          <a href="logout.php" class="block p-3 rounded-lg hover:bg-gray-50 font-medium text-gray-700">Logout</a>
        </nav>
      </aside>';
    }

}

/* $page = "Dashboard";
echo new Header()->header($page); */

