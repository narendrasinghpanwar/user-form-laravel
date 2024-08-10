<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Form</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-message {
            color: #dc3545;
            font-size: 80%;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>User Form</h1>
        <form id="userForm" enctype="multipart/form-data">
            <div class="mb-3 form-div">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name">
                <div class="error-message" id="nameError"></div>
            </div>
            <div class="mb-3 form-div">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
                <div class="error-message" id="emailError"></div>
            </div>
            <div class="mb-3 form-div">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone">
                <div class="error-message" id="phoneError"></div>
            </div>
            <div class="mb-3 form-div">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
                <div class="error-message" id="descriptionError"></div>
            </div>
            <div class="mb-3 form-div">
                <label for="role_id" class="form-label">Role</label>
                <select class="form-control" id="role_id" name="role_id">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <div class="error-message" id="roleError"></div>
            </div>
            <div class="mb-3 form-div">
                <label for="profile_image" class="form-label">Profile Image</label>
                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                <div class="error-message" id="profileImageError"></div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <div id="errorMessages" class="mt-3"></div>

        <h2 class="mt-5">Users</h2>
        <table class="table" id="usersTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Description</th>
                    <th>Role</th>
                    <th>Profile Image</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->description }}</td>
                        <td>{{ $user->role->name }}</td>
                        <td><img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile Image" width="50"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            $('#userForm input, #userForm select, #userForm textarea').on('input change', function() {
                $(this).closest('.form-div').find('.error-message').removeClass('is-invalid');
                $(this).closest('.form-div').find('.error-message').text('');
            });
            
            function validateForm() {
                let isValid = true;
                
                // Name validation
                if ($('#name').val().trim() === '') {
                    showError('name', 'Name is required');
                    isValid = false;
                } else {
                    clearError('name');
                }
                
                // Email validation
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test($('#email').val())) {
                    showError('email', 'Please enter a valid email address');
                    isValid = false;
                } else {
                    clearError('email');
                }
                
                // Phone validation
                const phonePattern = /^\+91\d{10}$/;
                const phoneInput = $('#phone');
                if (!phonePattern.test(phoneInput.val())) {
                    showError('phone', 'Please enter a valid Indian phone number starting with +91 followed by 10 digits');
                    isValid = false;
                } else {
                    clearError('phone');
                }
                
                // Role validation
                if ($('#role_id').val() === null) {
                    showError('role', 'Please select a role');
                    isValid = false;
                } else {
                    clearError('role');
                }
                
                // Profile image validation
                if ($('#profile_image').get(0).files.length === 0) {
                    showError('profileImage', 'Please select a profile image');
                    isValid = false;
                } else {
                    clearError('profileImage');
                }
                
                return isValid;
            }
            
            function showError(field, message) {
                $(`#${field}`).addClass('is-invalid');
                $(`#${field}Error`).text(message);
            }
            
            function clearError(field) {
                $(`#${field}`).removeClass('is-invalid');
                $(`#${field}Error`).text('');
            }


            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                var formData = new FormData(this);

                $.ajax({
                    url: '/api/users',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#errorMessages').html('');
                        $('#userForm')[0].reset();
                        
                        var newRow = '<tr>' +
                            '<td>' + response.user.name + '</td>' +
                            '<td>' + response.user.email + '</td>' +
                            '<td>' + response.user.phone + '</td>' +
                            '<td>' + response.user.description + '</td>' +
                            '<td>' + response.user.role.name + '</td>' +
                            '<td><img src="/storage/' + response.user.profile_image + '" alt="Profile Image" width="50"></td>' +
                            '</tr>';
                        
                        $('#usersTable tbody').append(newRow);
                        
                        alert('User created successfully!');
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessages = '<div class="alert alert-danger"><ul>';
                        $.each(errors, function(key, value) {
                            errorMessages += '<li>' + value + '</li>';
                        });
                        errorMessages += '</ul></div>';
                        $('#errorMessages').html(errorMessages);
                    }
                });
            }
            });
        });
    </script>
</body>
</html>
