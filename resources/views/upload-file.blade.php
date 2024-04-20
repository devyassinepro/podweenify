<!DOCTYPE html>
<html>
<head>
    <title>Laravel 9 Import Export Excel & CSV File to Database Example - LaravelTuts.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
     
<div class="container">
    <div class="card mt-3 mb-3">
        <div class="card-header text-center">
            <h4>Import Stores Csv to Weenify</h4>
        </div>
        <div class="card-body">
            <form action="/uploadcsv" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="mycsv" class="form-control">
                <br>
                <select name="category" id="category" class="form-control">
                    <option value="1">Dropshipping</option>
                    <option value="2">General</option>
                    <option value="3">T-shirt</option>
                    <option value="4">Digital</option>
                </select>
                <button class="btn btn-primary">Import Stores Data</button>
            </form>
        </div>
    </div>
</div>
     
</body>
</html>