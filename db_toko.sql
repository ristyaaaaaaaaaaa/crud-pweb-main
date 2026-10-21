/*
SQLyog Community v13.3.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - db_toko
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_toko` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `db_toko`;

/*Table structure for table `pembelian` */

DROP TABLE IF EXISTS `pembelian`;

CREATE TABLE `pembelian` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `no_transaksi` VARCHAR(25) NOT NULL,
  `tanggal` DATE NOT NULL,
  `supplier` VARCHAR(100) NOT NULL,
  `barang` VARCHAR(100) NOT NULL,
  `jumlah_barang` INT(11) NOT NULL,
  `total` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `pembelian` */

INSERT  INTO `pembelian`(`id`,`no_transaksi`,`tanggal`,`supplier`,`barang`,`jumlah_barang`,`total`) VALUES 
(1,'TRB-20260704-0001','2026-05-08','PT Global Teknologi','ASUS Vivobook S14',5,69995000);

/*Table structure for table `penjualan` */

DROP TABLE IF EXISTS `penjualan`;

CREATE TABLE `penjualan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `no_transaksi` VARCHAR(50) DEFAULT NULL,
  `tanggal` DATE DEFAULT NULL,
  `customer` VARCHAR(255) DEFAULT NULL,
  `barang` VARCHAR(255) DEFAULT NULL,
  `jumlah_barang` INT(11) NOT NULL DEFAULT 1,
  `total` DECIMAL(15,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `penjualan` */

INSERT  INTO `penjualan`(`id`,`no_transaksi`,`tanggal`,`customer`,`barang`,`jumlah_barang`,`total`) VALUES 
(1,'TRJ-20260704-0001','2026-04-05','Ni Kadek Ristya Dewi','ASUS TUF Gaming A15',3,79497000.00);

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` VARCHAR(50) DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `password` VARCHAR(50) DEFAULT NULL,
  `status` TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `user` */

INSERT  INTO `user`(`id`,`nama_lengkap`,`username`,`password`,`status`) VALUES 
(1,'Ni Kadek Ristya Dewi','Ristya','2501010183',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
