-- Esquema SQL para Farmacia Digital (MySQL)
-- Crea tablas: users, products, doctors, orders, order_items

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  correo VARCHAR(255) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  category VARCHAR(100) NOT NULL,
  description TEXT,
  icon VARCHAR(10),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  specialty VARCHAR(255),
  phone VARCHAR(50),
  email VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  nombre_cliente VARCHAR(255) NOT NULL,
  telefono VARCHAR(100),
  direccion TEXT,
  total DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  cantidad INT NOT NULL DEFAULT 1,
  subtotal DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Inserciones de ejemplo
INSERT INTO products (name, price, category, description, icon) VALUES
('Acetaminofén 500mg', 120.00, 'analgesicos', 'Alivio efectivo del dolor y fiebre', '💊'),
('Ibuprofeno 400mg', 150.00, 'analgesicos', 'Antiinflamatorio y analgésico', '💊'),
('Amoxicilina 500mg', 250.00, 'antibioticos', 'Antibiótico de amplio espectro', '🦠'),
('Vitamina C 1000mg', 90.00, 'vitaminas', 'Refuerza el sistema inmunológico', '🌟');

INSERT INTO doctors (name, specialty, phone, email) VALUES
('Dr. José Martínez','Medicina General','809-111-2222','jm@example.com'),
('Dra. Ana Pérez','Dermatología','809-333-4444','ap@example.com');

-- Usuario admin de ejemplo (contrasena: admin123) -> hash example
-- Recomendación: ejecutar INSERT con password_hash en PHP o manualmente.

-- ------------------------------------------------------------------
-- Tabla para farmaceuticos (pharmacists)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS farmaceuticos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  correo VARCHAR(255) NOT NULL UNIQUE,
  telefono VARCHAR(100),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserciones de ejemplo para farmaceuticos
INSERT INTO farmaceuticos (nombre, correo, telefono) VALUES
('Farmacéutico Juan López','juan.lopez@farmacia.com','809-444-5555'),
('Farmacéutica María Gómez','maria.gomez@farmacia.com','809-666-7777');

-- ------------------------------------------------------------------
-- ADMIN USER: seguridad y generación del hash
-- ------------------------------------------------------------------
-- Nota: el proyecto usa PHP con password_verify(), por lo que la contraseña
-- debe estar hasheada con password_hash(). No es recomendable incluir
-- contraseñas en texto plano en el repositorio. Abajo hay dos opciones:
-- 1) Ejecutar el script PHP proporcionado en `sql/generate_admin_sql.php` para
--    generar un INSERT listo con el hash para la contraseña `Admin@123!`.
--    Ejemplo de uso (desde la raíz del repo):
--      php sql/generate_admin_sql.php
--    El script imprimirá en pantalla el INSERT SQL que puedes ejecutar
--    contra tu base de datos.

-- 2) Si prefieres insertar directamente (no recomendado), reemplaza
--    el valor de `CONTRASENA_HASH_PLACEHOLDER` por un hash generado
--    previamente con password_hash.

-- Inserción de admin (comentada): ejecutar el INSERT generado por el script PHP
-- o descomentar y reemplazar el hash por uno válido.
-- INSERT INTO users (nombre, correo, contrasena, role) VALUES
-- ('Administrador', 'admin@farmacia.local', 'CONTRASENA_HASH_PLACEHOLDER', 'admin');

-- ------------------------------------------------------------------
-- Productos: por volumen, los datos de productos se colocan en
-- `sql/products_seed.sql`. Para importar todo en MySQL:
-- 1) Crear la base de datos y usarla (CREATE DATABASE + USE nombre_db)
-- 2) Ejecutar este archivo `schema.sql` para crear tablas
-- 3) Ejecutar `source sql/products_seed.sql;` o importar `sql/products_seed.sql`
-- ------------------------------------------------------------------
