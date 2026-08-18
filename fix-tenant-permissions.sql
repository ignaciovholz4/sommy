-- Fix permissions for all existing tenant databases
-- Run this in your database container

-- Grant laravel user access to all tenant databases
GRANT ALL PRIVILEGES ON `tenant_%`.* TO 'laravel'@'%';
GRANT ALL PRIVILEGES ON `tenant_%`.* TO 'laravel'@'localhost';

-- Also grant for specific tenant_empresa if it exists
GRANT ALL PRIVILEGES ON `tenant_empresa`.* TO 'laravel'@'%';
GRANT ALL PRIVILEGES ON `tenant_empresa`.* TO 'laravel'@'localhost';

FLUSH PRIVILEGES;

-- Show grants for verification
SHOW GRANTS FOR 'laravel'@'%';
