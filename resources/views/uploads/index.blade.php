<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CSV Uploads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/js/app.js'])

    <style>
        #loader-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            text-align: center;
        }

        #loader-overlay .spinner-border {
            margin-top: 20%;
        }

        #loader-text {
            margin-top: 1rem;
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
        }
    </style>
</head>

<body class="p-5 bg-light">
    <div id="loader-overlay">
        <div class="spinner-border text-primary" role="status"></div>
        <div id="loader-text">Uploading... this may take a moment.</div>
    </div>

    <div class="container">
        <h2 class="mb-4">CSV Upload System</h2>

        <div class="card mb-4">
            <div class="card-body">
                <form id="upload-form" action="{{ route('uploads.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                            required> <button class="btn btn-primary" type="submit">Upload</button>

                        @error('file')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Recent Uploads</h5>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Filename</th>
                            <th>Status</th>
                            <th>Processed At</th>
                        </tr>
                    </thead>
                    <tbody id="upload-table-body">
                        @foreach ($uploads as $upload)
                            <tr id="upload-row-{{ $upload->id }}">
                                <td>{{ $upload->id }}</td>
                                <td>{{ $upload->file_name }}</td>
                                <td class="status">{{ ucfirst($upload?->status->value) }}</td>
                                <td>{{ $upload->processed_at ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('upload-form').addEventListener('submit', function() {
            document.getElementById('loader-overlay').style.display = 'block';
        });
    </script>
</body>

</html>
