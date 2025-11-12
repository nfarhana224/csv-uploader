## 1. CSV File Uploader Assignment

This is Laravel application for uploading CSV files with background processing, as refer to the requirement:

- CSV file upload with drag & drop or selector
- Background processing with Laravel Queues + Redis
- Real-time status updates with polling
- UPSERT functionality using UNIQUE_KEY
- UTF-8 character cleaning
- Idempotent file uploads
- History in the list table with status tracking
- API endpoints with Transformers


## 2. Technical Implementation


### Background Processing
- Implemented using Laravel Queues with Redis driver
- Real-time status updates via AJAX polling (as per requirement choice)
- File processing in chunks for large file support (if more than 10MB file)

### Platform Compatibility Note
**Horizon** was recommended but could not be implemented due to platform constraints.
This project was developed on a Windows (Horizon not compatible with Windows environment)


## 3. Installation (Step-By-Step)

Using Windows PowerShell to start the installation setup

### - Set path location of original folder:
cd C:\

### - Clone repository:
git clone https://github.com/nfarhana224/csv-uploader.git

### - Set path location of the project:
cd csv-uploader

### - Install dependencies:
composer install

### - Environment setup (Copy `.env.example` to `.env`):
copy .env.example .env

### - Generate application key:
php artisan key:generate

### Database setup (type in "yes"):
php artisan migrate

###  - Queue Setup with Redis at Terminal 1:
php artisan queue:work --timeout=1200

### - Start Laravel server at Terminal 2:
php artisan serve


## 4. API Endpoints

- `GET /api/uploads` - List all uploads (with transformers)
- `GET /api/uploads/{id}` - Get specific upload details


## 5. Usage

1. Access the application at `http://localhost:8000`
2. Upload CSV file via drag & drop or file selector
3. View real-time processing status in the history table
4. Receive browser notifications when processing completes


## 6. Development Notes

- **Polling** chosen over WebSockets as per requirement flexibility
- **Redis** used for queue driver as specified
- **Windows compatibility** considered in implementation choices
