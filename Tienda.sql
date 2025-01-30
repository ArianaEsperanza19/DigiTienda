CREATE DATABASE digitienda;

USE digitienda;

DROP TABLE producto;

CREATE TABLE usuarios (
    id INT(255) auto_increment NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    rol VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    permisos INT(10) NOT NULL,
    CONSTRAINT pk_Usuarios PRIMARY KEY (id),
    CONSTRAINT uq_email UNIQUE (email)
) ENGINE = InnoDB;

CREATE TABLE categorias (
    id INT(255) auto_increment NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    CONSTRAINT pk_Categorias PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE productos (
    id INT(255) auto_increment NOT NULL,
    categoria_id INT(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    precio FLOAT(255, 2) NOT NULL,
    descripcion MEDIUMTEXT NOT NULL,
    stock INT(100) NOT NULL,
    imagen VARCHAR(255),
    CONSTRAINT pk_Productos PRIMARY KEY (id),
    CONSTRAINT pf_Productos FOREIGN KEY (categoria_id) REFERENCES categorias (id)
) ENGINE = InnoDB;

CREATE TABLE pedidos (
    id INT(255) auto_increment NOT NULL,
    usuario_id INT(255) NOT NULL,
    provincia VARCHAR(100),
    ciudad VARCHAR(100),
    direccion VARCHAR(255),
    coste FLOAT(255, 2),
    fecha DATE NOT NULL,
    unidades INT(255),
    status VARCHAR(20) NOT NULL,
    CONSTRAINT pk_Pedidos PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE lineas_Pedidos (
    id INT(255) auto_increment NOT NULL,
    pedido_id INT(255) NOT NULL,
    producto_id INT(255) NOT NULL,
    unidades INT(255) NOT NULL,
    CONSTRAINT pk_Lineas PRIMARY KEY (id)
) ENGINE = InnoDB;