import os

# Define the folder structure for the admin folder
admin_folder_structure = [
    "admin/dashboard.php",
    "admin/manage-users.php",
    "admin/manage-papers.php",
    "admin/journal-settings.php",
    "admin/navbar.php",
    "admin/menu.php",
    "admin/manage_reviews.php",
    "admin/manage_payments.php",
    "admin/seo_settings.php",
    "admin/update_issue_details.php",
    "admin/control_menu_items.php",
    "admin/journal_information.php"
]

# Define initial content for key files in the admin folder
admin_file_contents = {
    "admin/dashboard.php": """<?php
require '../includes/header.php';
echo '<h1>Admin Dashboard</h1>';
require '../includes/footer.php';
?>""",
    "admin/manage-users.php": """<?php
require '../includes/header.php';
echo '<h1>Manage Users</h1>';
require '../includes/footer.php';
?>""",
    "admin/manage-papers.php": """<?php
require '../includes/header.php';
echo '<h1>Manage Papers</h1>';
require '../includes/footer.php';
?>""",
    "admin/journal-settings.php": """<?php
require '../includes/header.php';
echo '<h1>Journal Settings</h1>';
require '../includes/footer.php';
?>""",
    "admin/navbar.php": """<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">IJTER Admin</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="nav-link" href="#">Dashboard <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Manage Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Manage Papers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Journal Settings</a>
            </li>
        </ul>
    </div>
</nav>""",
    "admin/menu.php": """<div class="list-group">
    <a href="#" class="list-group-item list-group-item-action active">
        Dashboard
    </a>
    <a href="#" class="list-group-item list-group-item-action">Manage Users</a>
    <a href="#" class="list-group-item list-group-item-action">Manage Papers</a>
    <a href="#" class="list-group-item list-group-item-action">Journal Settings</a>
    <a href="#" class="list-group-item list-group-item-action">Manage Reviews</a>
    <a href="#" class="list-group-item list-group-item-action">Manage Payments</a>
    <a href="#" class="list-group-item list-group-item-action">SEO Settings</a>
    <a href="#" class="list-group-item list-group-item-action">Update Issue Details</a>
    <a href="#" class="list-group-item list-group-item-action">Control Menu Items</a>
    <a href="#" class="list-group-item list-group-item-action">Journal Information</a>
</div>""",
    "admin/manage_reviews.php": """<?php
require '../includes/header.php';
echo '<h1>Manage Reviews</h1>';
require '../includes/footer.php';
?>""",
    "admin/manage_payments.php": """<?php
require '../includes/header.php';
echo '<h1>Manage Payments</h1>';
require '../includes/footer.php';
?>""",
    "admin/seo_settings.php": """<?php
require '../includes/header.php';
echo '<h1>SEO Settings</h1>';
require '../includes/footer.php';
?>""",
    "admin/update_issue_details.php": """<?php
require '../includes/header.php';
echo '<h1>Update Issue Details</h1>';
require '../includes/footer.php';
?>""",
    "admin/control_menu_items.php": """<?php
require '../includes/header.php';
echo '<h1>Control Menu Items</h1>';
require '../includes/footer.php';
?>""",
    "admin/journal_information.php": """<?php
require '../includes/header.php';
echo '<h1>Journal Information</h1>';
require '../includes/footer.php';
?>"""
}

# Function to create the folder structure and files
def create_admin_structure(base_path, structure, contents):
    for item in structure:
        item_path = os.path.join(base_path, item)
        if '.' in item:
            # Ensure the directory exists before creating the file
            os.makedirs(os.path.dirname(item_path), exist_ok=True)
            # Create a file and write initial content if available
            with open(item_path, 'w') as f:
                if item in contents:
                    f.write(contents[item])
        else:
            # Create a directory
            os.makedirs(item_path, exist_ok=True)

# Base path where the project structure will be created
base_path = "."

# Create the admin folder structure
create_admin_structure(base_path, admin_folder_structure, admin_file_contents)

print("Admin folder structure created successfully.")
