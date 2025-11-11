<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Uploader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .table-border {
            border: 1px solid #e5e7eb;
        }
        .cell-border {
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <h1 class="text-3xl font-bold text-center mb-8">CSV File Uploader Assignment</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Upload Section - COMPACT -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data" 
                  x-data="{
                      selectedFile: null,
                      handleFileSelect(event) {
                          const file = event.target.files[0];
                          if (file) {
                              this.selectedFile = file.name;
                          }
                      },
                      handleDrop(event) {
                          event.preventDefault();
                          const files = event.dataTransfer.files;
                          if (files.length > 0) {
                              const file = files[0];
                              if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                                  this.selectedFile = file.name;
                                  const dataTransfer = new DataTransfer();
                                  dataTransfer.items.add(file);
                                  document.getElementById('csv_file').files = dataTransfer.files;
                              }
                          }
                      }
                  }"
                  @drop="handleDrop($event)" 
                  @dragover.prevent
                  @dragenter.prevent>
                
                @csrf
                
                <div class="flex gap-3 items-center">
                    <!-- Drag & Drop Area - COMPACT -->
                    <div class="flex-1">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center"
                             @drop="handleDrop($event)" 
                             @dragover.prevent
                             @dragenter.prevent>
                            
                            <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" 
                                   class="hidden" @change="handleFileSelect">
                            
                            <label for="csv_file" class="cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span class="text-gray-600 font-medium">Select file / Drag and drop</span>
                            </label>
                            
                            <!-- Selected File Display - COMPACT -->
                            <div x-show="selectedFile" class="mt-2 p-2 bg-blue-50 rounded text-sm">
                                <p class="text-blue-700" x-text="selectedFile"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Button - COMPACT -->
                    <div class="flex-shrink-0">
                        <button type="submit" 
                                class="bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-200 font-medium whitespace-nowrap">
                            Upload File
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Upload History Section dengan BORDER -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Record uploads</h2>
            
            <!-- Table dengan BORDER seperti image -->
            <div class="overflow-x-auto">
                <table class="w-full table-border border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="text-left py-3 px-4 font-medium text-gray-600 cell-border border-r-0">Time</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-600 cell-border border-r-0 border-l-0">File Name</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-600 cell-border border-l-0">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uploads as $upload)
                        <tr class="hover:bg-gray-50">
                            <!-- Time Column -->
                            <td class="py-4 px-4 cell-border border-r-0 border-t-0">
                                <div class="text-gray-800">
                                    {{ $upload->created_at->format('m-d-y g:ia') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    ({{ $upload->created_at->diffForHumans() }})
                                </div>
                            </td>
                            
                            <!-- File Name Column -->
                            <td class="py-4 px-4 text-gray-800 cell-border border-r-0 border-l-0 border-t-0">
                                {{ $upload->filename }}
                            </td>
                            
                            <!-- Status Column -->
                            <td class="py-4 px-4 cell-border border-l-0 border-t-0">
                                @if($upload->status === 'pending')
                                    <span class="text-yellow-600 font-medium">pending</span>
                                @elseif($upload->status === 'processing')
                                    <span class="text-blue-600 font-medium">processing</span>
                                @elseif($upload->status === 'completed')
                                    <span class="text-green-600 font-medium">completed</span>
                                @elseif($upload->status === 'failed')
                                    <span class="text-red-600 font-medium">failed</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        @if($uploads->isEmpty())
                        <tr>
                            <td colspan="3" class="py-8 px-4 text-center text-gray-500 cell-border border-t-0">
                                No uploads yet. Upload your first CSV file!
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Alpine.js -->
      <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
    function uploadStatus() {
        return {
            uploads: @json($uploads),
            previousStatus: {},
            
            init() {
                // Store initial status untuk compare changes
                this.uploads.forEach(upload => {
                    this.previousStatus[upload.id] = upload.status;
                });
                
                // Start polling
                this.startPolling();
                
                // Request notification permission
                this.requestNotificationPermission();
            },
            
            startPolling() {
                setInterval(() => {
                    this.fetchUploads();
                }, 2000); // Poll setiap 2 saat
            },
            
            fetchUploads() {
                fetch('/api/uploads')
                    .then(response => response.json())
                    .then(data => {
                        // Check untuk status changes
                        this.checkStatusChanges(data);
                        
                        // Update uploads data
                        this.uploads = data;
                    })
                    .catch(error => console.error('Error fetching uploads:', error));
            },
            
            checkStatusChanges(newUploads) {
                newUploads.forEach(upload => {
                    const previous = this.previousStatus[upload.id];
                    const current = upload.status;
                    
                    // Jika status berubah dari processing ke completed
                    if (previous === 'processing' && current === 'completed') {
                        this.showCompletionNotification(upload.filename);
                    }
                    
                    // Jika status berubah dari pending ke failed
                    if (previous === 'processing' && current === 'failed') {
                        this.showErrorNotification(upload.filename);
                    }
                    
                    // Update previous status
                    this.previousStatus[upload.id] = current;
                });
            },
            
            showCompletionNotification(filename) {
                // Browser Notification
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('CSV Processing Complete', {
                        body: `File "${filename}" has been processed successfully!`,
                        icon: '/favicon.ico'
                    });
                }
                
                // Toast Notification
                this.showToastNotification(`File "${filename}" processing completed!`, 'success');
            },
            
            showErrorNotification(filename) {
                this.showToastNotification(`File "${filename}" processing failed!`, 'error');
            },
            
            showToastNotification(message, type = 'success') {
                const container = document.getElementById('notification-container');
                const notification = document.createElement('div');
                
                const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                const icon = type === 'success' ? 
                    '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>' :
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
                
                notification.className = `${bgColor} text-white p-4 rounded-lg shadow-lg transform transition-transform duration-300 translate-x-full`;
                notification.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                ${icon}
                            </svg>
                            <span>${message}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                `;
                
                container.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Auto remove after 5s
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 5000);
            },
            
            requestNotificationPermission() {
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }
            },
            
            formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleDateString('en-US', {
                    month: '2-digit',
                    day: '2-digit',
                    year: '2-digit'
                }) + ' ' + date.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }).toLowerCase();
            },
            
            formatRelativeTime(timestamp) {
                const date = new Date(timestamp);
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);
                
                if (diffInSeconds < 60) return '(just now)';
                if (diffInSeconds < 3600) return `(${Math.floor(diffInSeconds / 60)} minutes ago)`;
                if (diffInSeconds < 86400) return `(${Math.floor(diffInSeconds / 3600)} hours ago)`;
                return `(${Math.floor(diffInSeconds / 86400)} days ago)`;
            }
        }
    }
    </script>
</body>
</html>