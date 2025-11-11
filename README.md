## CSV File Uploader Assignment

This is Laravel application for uploading CSV files with background processing, as refer to the requirement:

- CSV file upload with drag & drop or selector
- Background processing with Laravel Queues + Redis
- Real-time status updates with polling
- UPSERT functionality using UNIQUE_KEY
- UTF-8 character cleaning
- Idempotent file uploads
- History in the list table with status tracking
- API endpoints with Transformers

## Technical Implementation

### Background Processing
- Implemented using Laravel Queues with Redis driver
- Real-time status updates via AJAX polling (as per requirement choice)
- File processing in chunks for large file support (if more than 10MB file)

### Platform Compatibility Note
**Horizon** was recommended but could not be implemented due to platform constraints.
This project was developed on a Windows (Horizon not compatible with Windows environment)

## Installation

1. Clone repository: `git clone https://github.com/nfarhana224/csv-uploader.git`
2. Install dependencies: `composer install`
3. Environment setup: `cp .env.example .env` and `php artisan key:generate`
4. Database: `php artisan migrate`
5. Queue: Start Redis server and run `php artisan queue:work`
6. Serve: `php artisan serve`

## API Endpoints

- `GET /api/uploads` - List all uploads (with transformers)
- `GET /api/uploads/{id}` - Get specific upload details

## Usage

1. Access the application at `http://localhost:8000`
2. Upload CSV file via drag & drop or file selector
3. View real-time processing status in the history table
4. Receive browser notifications when processing completes

## Development Notes

- **Polling** chosen over WebSockets as per requirement flexibility
- **Redis** used for queue driver as specified
- **Windows compatibility** considered in implementation choices