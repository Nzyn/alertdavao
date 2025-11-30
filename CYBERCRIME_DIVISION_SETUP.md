# Cybercrime Division Setup Guide

The Cybercrime Division has been added to the system. Follow these steps to enable it:

## Option 1: Run Migration (Recommended)

If you haven't run migrations yet, simply run:

```bash
cd AdminSide/admin
php artisan migrate
```

This will automatically create the Cybercrime Division police station in your database.

## Option 2: Run Seeder

If migrations have already been run, use the seeder:

```bash
cd AdminSide/admin
php artisan db:seed --class=PoliceStationsSeeder
```

## Option 3: Manual Database Insertion

If neither of the above works, directly insert into the database:

```sql
INSERT INTO police_stations (station_name, address, latitude, longitude, contact_number, created_at, updated_at)
VALUES (
    'Cybercrime Division',
    'Davao City Police Office - Cybercrime Division',
    0,
    0,
    'TBD',
    NOW(),
    NOW()
);
```

## Verify Setup

1. Login to AdminSide Dashboard
2. Go to Personnel Management
3. Click "Assign Police to Station"
4. You should now see **Cybercrime Division** in the list of available stations

## What Happens Next

- Users can now submit cybercrime reports from UserSide
- Cybercrime keywords detected: hacking, phishing, ransomware, cyber fraud, identity theft, etc.
- All cybercrime reports are automatically routed to the Cybercrime Division
- Police officers assigned to Cybercrime Division will see these reports in their dashboard

## Cybercrime Report Detection

Reports are automatically classified as cybercrime if they contain keywords such as:
- cybercrime, cyber crime
- hacking, phishing
- ransomware, malware
- identity theft, online fraud
- data breach, unauthorized access

These reports are routed globally to the Cybercrime Division regardless of the user's location.
