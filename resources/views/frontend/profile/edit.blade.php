<x-app-layout>
    <!-- Page Content -->
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Update Profile</h1>
        <form id="update-profile-form">
            <div>
                <label for="name" class="block">Name:</label>
                <input type="text" id="name" class="form-input" value="{{ $user->name }}">
                <div id="name-error" class="text-red-500"></div>
            </div>

            <div>
                <label for="email" class="block">Email:</label>
                <input type="email" id="email" class="form-input" value="{{ $user->email }}">
                <div id="email-error" class="text-red-500"></div>
            </div>

            <!-- Add other fields based on your schema -->

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 mt-4">Update Profile</button>
        </form>
        <div id="message" class="mt-2"></div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.getElementById('update-profile-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('email', document.getElementById('email').value);
        // Append other form fields accordingly

        axios.put('/api/user/{{ $user->id }}', formData)
            .then(function(response) {
                document.getElementById('message').innerHTML = '<p class="text-green-500">Profile updated successfully!</p>';
            })
            .catch(function(error) {
                if (error.response.status === 422) {
                    const errors = error.response.data.errors;
                    for (const key in errors) {
                        document.getElementById(`${key}-error`).innerText = errors[key][0];
                    }
                } else {
                    document.getElementById('message').innerHTML = '<p class="text-red-500">An error occurred while updating profile.</p>';
                }
            });
    });
</script>
