-- Add Cybercrime Division Police Station
-- This station receives all cybercrime-type reports globally (no location-based assignment)

USE alertdavao;

-- Insert Cybercrime Division if it doesn't exist
INSERT INTO police_stations (station_name, address, latitude, longitude, contact_number) 
VALUES ('Cybercrime Division', 'Davao City Police Office - Cybercrime Division', 0, 0, 'TBD')
ON DUPLICATE KEY UPDATE station_name='Cybercrime Division';

-- Verify insertion
SELECT * FROM police_stations WHERE station_name = 'Cybercrime Division';
