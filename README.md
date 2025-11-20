# Vendor Management System

A comprehensive vendor onboarding and approval workflow system.

## Overview

This system manages the complete lifecycle of vendor registration, from initial creation through multi-stage approval to final approval. It features a robust state machine, full audit trail, and role-based workflow management.

## Features

### Core Workflow
- **8-Stage Pipeline**: New → With Vendor → Checker Review → Procurement Review → Legal Review → Finance Review → Directors Review → Approved
- **Multi-Role System**: Initiator, Vendor, Checker, Procurement, Legal, Finance, Director
- **Approve/Reject Actions**: Each reviewer can approve (move forward) or reject (send back to vendor)
- **Resubmission Flow**: Rejected vendors can correct issues and resubmit

### Key Capabilities
- Complete audit trail (every action logged with timestamp and actor)
- Document management (vendors can attach multiple documents)
- Approved vendors masterlist with search and filtering
- "Who acts next" indicators for workflow clarity
- Status badges (Pending/Approved/Rejected)
- Comment system (mandatory for rejections)

## Tech Stack

**Backend:**
- Laravel 11 (PHP 8.2)
- PostgreSQL 15

**Frontend:**
- Blade Templates
- Tailwind CSS (via CDN)
- Alpine.js (via Breeze)

**Infrastructure:**
- Docker & Docker Compose
- Nginx/PHP-FPM

## Prerequisites

- Docker Desktop installed
- Composer installed (for local development)
- Node.js & npm (for asset compilation)

## Setup Instructions

### 1. Clone & Setup
```bash
# Clone repository
git clone <https://github.com/E-ugine/vendor_management.git>
cd vendor_management

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Configure Environment

Update `.env` with these settings:
```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=vendor_management
DB_USERNAME=vms_user
DB_PASSWORD=yourpassword

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### 3. Start Docker
```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Seed demo users
docker-compose exec app php artisan db:seed

# Build frontend assets
npm run build
```

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear

# Access database
docker-compose exec db psql -U vms_user -d vendor_management

# View logs
docker-compose exec app tail -f storage/logs/laravel.log

# Stop environment
docker-compose down



## Troubleshooting

**Port 5432 already in use:**
```bash
# Stop local PostgreSQL
brew services stop postgresql

# Or change Docker port in docker-compose.yml:
ports:
  - "5432:5432" to "5432:5432" // This might be crucial if you're connecting to a GUI client like Beekeper Studio or Dbeaver.
```

**Application shows "Production" warning:**
- This is normal - APP_ENV=production in .env for demonstration
- Migrations require confirmation in production mode

### 4. Access Application

Visit: **http://localhost:8000**

## Demo Users

| Role | Email | Password | Purpose |
|------|-------|----------|---------|
| Initiator | initiator@test.com | password | Create new vendors |
| Vendor | vendor@test.com | password | Complete vendor details |
| Checker | checker@test.com | password | First review stage |
| Procurement | procurement@test.com | password | Procurement review |
| Legal | legal@test.com | password | Legal compliance review |
| Finance | finance@test.com | password | Financial review |
| Director | director@test.com | password | Final approval authority |

**Note:** All users share the same password for demo convenience. Use the role switcher in the navigation to test different workflows without logging out.

## Testing the Workflow

### Complete Approval Flow

1. **Login** as any user (e.g., `initiator@test.com` / `password`)
2. **Switch to Initiator** role (dropdown in top-right nav)
3. **Create vendor**: Dashboard → "Create New Vendor" → Fill form → Submit
4. **Switch to Vendor** role
5. **Complete details**: Click vendor → "Edit & Submit" → Add documents → "Submit for Review"
6. **Switch to Checker** role
7. **Review**: Click vendor → Add comment (optional) → "Approve"
8. **Repeat steps 6-7** for Procurement, Legal, Finance, and Director roles
9. **View approved**: Navigate to `/approved-vendors` to see the masterlist

### Rejection & Resubmission Flow

1. Follow steps 1-6 above
2. As Checker, add comment "Missing tax documents" → "Reject"
3. Vendor goes back to "With Vendor" stage (status: Rejected)
4. Switch to Vendor role
5. Edit vendor → Add missing info → "Submit for Review"
6. Status resets to Pending, sent back to Checker
7. Continue approval flow normally

## Key Routes

| URL | Purpose |
|-----|---------|
| `/dashboard` | Main dashboard with role-based actions |
| `/vendors` | List all vendors |
| `/vendors/create` | Create new vendor (Initiator) |
| `/vendors/{id}` | View vendor details & history |
| `/vendors/{id}/edit` | Edit vendor details (Vendor role) |
| `/approved-vendors` | Approved vendors masterlist |

## Architecture Highlights

### State Machine Pattern
The `Vendor` model uses enum-based state transitions to ensure vendors move through stages sequentially. The `transitionTo()` method handles:
- Stage updates
- Status management (pending/approved/rejected)
- Automatic history logging
- Database transactions for consistency

### Audit Trail
Every action creates an immutable history record with:
- Stage where action occurred
- Action type (created/submitted/approved/rejected)
- Actor (who took the action)
- Timestamp
- Optional comment

### Database Design
- **vendors**: Core vendor data with current_stage and status
- **vendor_documents**: Simple document tracking (file names for MVP)
- **vendor_stage_history**: Complete audit log
- **users**: Demo users with role switcher capability

## Assumptions & Trade-offs

### Decisions Made for MVP

1. **Role Switcher over RBAC**: Task specified "simple role switching" rather than complex permissions. Production would use proper role/permission tables.

2. **File Names Only**: Documents are stored as text fields (file names) rather than actual file uploads. This demonstrates the concept without S3/storage complexity.

3. **File-based Cache**: Used file cache instead of Redis for simplicity. Production would use Redis for session/cache.

4. **Sequential Stages**: Vendors must progress through all stages in order. No skipping allowed, enforced by enum logic.

5. **Single Approval Path**: No parallel approvals or conditional routing. Linear workflow only.

6. **Rejection Always to "With Vendor"**: All rejections send vendor back to the vendor stage, regardless of where rejection occurred.

### Production Considerations

For production deployment, these would be added:
- Real file upload with S3 storage
- Email notifications at each stage
- SLA timers and escalation
- Advanced search (Elasticsearch)
- API endpoints for mobile app
- Proper RBAC with permissions
- Audit export functionality
- Vendor dashboard analytics

## Testing

Run feature tests:
```bash
docker-compose exec app php artisan test
```

## Project Structure
```
app/
├── Enums/              # VendorStage, VendorStatus, UserRole
├── Models/             # Vendor, VendorDocument, VendorStageHistory
├── Http/
│   ├── Controllers/    # VendorController
│   └── Requests/       # StoreVendorRequest, UpdateVendorRequest
resources/
├── views/
│   ├── vendors/        # All vendor-related views
│   └── layouts/        # App layout with role switcher
database/
├── migrations/         # Schema definitions
└── seeders/           # Demo user seeder
```

  
