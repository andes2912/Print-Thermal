<?php
/* Call this file 'hello-world.php' */
require '../vendor/autoload.php';
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

$connector = new WindowsPrintConnector("PrinterThermal1");
$printer = new Printer($connector);

//printah untuk membuka laci / cash drawer
$printer->pulse();

/* mulai cetak */
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("PT. PRIMA ANUGRAH SEJAHTERAH NUSANTARA \n");
$printer->setFont(Printer::FONT_B);
$printer->text("PELABUHAN TANJUNG REDEB, BERAU, KALIMANTAN TIMUR\n");
$printer->text("-----------------------------\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
// $printer->text(($_POST['type'] == 'sp2') ? 'INVOICE SP2 BONGKAR' : (($_POST['type'] == 'container_storage') ? 'Perpanjangan Storage' : 'INVOICE SP2 MUAT'));
if ($_POST['type'] == 'sp2') {
    $printer->text('INVOICE SP2 BONGKAR');
} elseif($_POST['type'] == 'sp2_muat') {
    $printer->text('INVOICE SP2 MUAT');
} elseif ($_POST['type'] == 'container_storage') {
    $printer->text('INVOICE PERPANJANGAN STORAGE');
} elseif ($_POST['type'] == 'sp2_out') {
    $printer->text('INVOICE STRIPPING INTERCHANGE');
} elseif($_POST['type'] == 'jasa_muat') {
    $printer->text('INVOICE STEVEDORING');
} elseif($_POST['type'] == 'sp_bongkar') {
  $printer->text('JASA SPESIAL PRICE BONGKAR');
} elseif($_POST['type'] == 'sp_muat') {
  $printer->text('JASA SPESIAL PRICE BONGKAR');
}

$printer->text("\n");
$printer->text("".date('Y:m:d h:i:s')." \n");
$printer->setEmphasis(true);
$printer->text("NO. INVOICE: ".$_POST['invoice_number']." \n");
$printer->text("\n");
// $printer->text("-----------------------------\n");

// membuat fungsi untuk membuat 1 baris tabel, agar dapat dipanggil berkali-kali dgn mudah
function buatBaris4Kolom($kolom1, $kolom2, $kolom3) {
    // Mengatur lebar setiap kolom (dalam satuan karakter)
    $lebar_kolom_1 = 18;
    $lebar_kolom_2 = 22;
    $lebar_kolom_3 = 16;
    // $lebar_kolom_4 = 9;

    // Melakukan wordwrap(), jadi jika karakter teks melebihi lebar kolom, ditambahkan \n
    $kolom1 = wordwrap($kolom1, $lebar_kolom_1, "\n", true);
    $kolom2 = wordwrap($kolom2, $lebar_kolom_2, "\n", true);
    $kolom3 = wordwrap($kolom3, $lebar_kolom_3, "\n", true);
    // $kolom4 = wordwrap($kolom4, $lebar_kolom_4, "\n", true);

    // Merubah hasil wordwrap menjadi array, kolom yang memiliki 2 index array berarti memiliki 2 baris (kena wordwrap)
    $kolom1Array = explode("\n", $kolom1);
    $kolom2Array = explode("\n", $kolom2);
    $kolom3Array = explode("\n", $kolom3);
    // $kolom4Array = explode("\n", $kolom4);

    // Mengambil jumlah baris terbanyak dari kolom-kolom untuk dijadikan titik akhir perulangan
    $jmlBarisTerbanyak = max(count($kolom1Array), count($kolom2Array), count($kolom3Array));

    // Mendeklarasikan variabel untuk menampung kolom yang sudah di edit
    $hasilBaris = array();

    // Melakukan perulangan setiap baris (yang dibentuk wordwrap), untuk menggabungkan setiap kolom menjadi 1 baris
    for ($i = 0; $i < $jmlBarisTerbanyak; $i++) {

        // memberikan spasi di setiap cell berdasarkan lebar kolom yang ditentukan,
        $hasilKolom1 = str_pad((isset($kolom1Array[$i]) ? $kolom1Array[$i] : ""), $lebar_kolom_1, " ");
        $hasilKolom2 = str_pad((isset($kolom2Array[$i]) ? $kolom2Array[$i] : ""), $lebar_kolom_2, " ");

        // memberikan rata kanan pada kolom 3 dan 4 karena akan kita gunakan untuk harga dan total harga
        $hasilKolom3 = str_pad((isset($kolom3Array[$i]) ? $kolom3Array[$i] : ""), $lebar_kolom_3, " ");
        // $hasilKolom4 = str_pad((isset($kolom4Array[$i]) ? $kolom4Array[$i] : ""), $lebar_kolom_4, " ", STR_PAD_LEFT);

        // Menggabungkan kolom tersebut menjadi 1 baris dan ditampung ke variabel hasil (ada 1 spasi disetiap kolom)
        $hasilBaris[] = $hasilKolom1 . " " . $hasilKolom2 . " " . $hasilKolom3 ;
    }

    // Hasil yang berupa array, disatukan kembali menjadi string dan tambahkan \n disetiap barisnya.
    return implode($hasilBaris) . "\n";
}

    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_A);
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom('Pelayaran', $_POST['maskapai_name'],''));
    $printer->text(buatBaris4Kolom('Kapal/Voyage', $_POST['nama_kapal'],$_POST['voyage']));
    $printer->text(buatBaris4Kolom('Customer', $_POST['corporate_name'],''));

// Membuat tabel
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->initialize(); // Reset bentuk/jenis teks
$printer->text("-------------------------------------------\n");
$printer->setFont(Printer::FONT_B);
$total = 0;
$total1 = 0;
if ($_POST['type'] == 'sp2') {
    $printer->setEmphasis(true);
    $printer->text("Biaya SP2 Bongkar \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['container_type'],
            number_format($item['total']),
        ));
       $total2 = $item['sub_total'];
    }

    if ($_POST['isContainerStorage'] == 1) {
        // PENUMPUKAN MASA
        $printer->setEmphasis(true);
        $printer->text("Biaya Perpanjangan Storage \n");
        $printer->initialize(); // Reset bentuk/jenis teks
        $printer->setFont(Printer::FONT_B);
        $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
        $printer->text("----------------------------------------------------------\n");
        foreach ($_POST['container_storage_items'] as $item) {
            $renewal = date_create($item['renewal_date']);
            $printer->text(buatBaris4Kolom(
                strtoupper($item['container']),
                $item['container_type'] .'('.$item['days'] .' hari)' ."\n" .'s.d ' .date_format($renewal,"d F Y"),
                "\n".number_format($item['total']),
            ));
            $total = $item['sub_total'];

        }
    }

    if ($_POST['isSp2Out'] == 1) {
        $printer->setEmphasis(true);
        $printer->text("Biaya Stripping Interchange \n");
        $printer->initialize(); // Reset bentuk/jenis teks
        $printer->setFont(Printer::FONT_B);
        $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
        $printer->text("----------------------------------------------------------\n");
        foreach ($_POST['sp2_out_items'] as $item) {
            $printer->text(buatBaris4Kolom(
                strtoupper($item['container']),
                $item['name'],
                number_format($item['total']),
            ));
            $total1 = $item['sub_total'];
        }
    }

    if ($_POST['isJasaBongkar'] == 1) {
        $printer->setEmphasis(true);
        $printer->text("Biaya Stevedoring+LOLO \n");
        $printer->initialize(); // Reset bentuk/jenis teks
        $printer->setFont(Printer::FONT_B);
        $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
        $printer->text("----------------------------------------------------------\n");
        foreach ($_POST['sp2_out_items'] as $item) {
            $printer->text(buatBaris4Kolom(
                strtoupper($item['container']),
                $item['name'],
                number_format($item['total']),
            ));
            $total7 = $item['sub_total'];
        }
    }

}elseif($_POST['type'] == 'sp2_muat') {
    $printer->setEmphasis(true);
    $printer->text("Biaya SP2 Muat \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['container_type'],
            number_format($_POST['sub_total'] / count($_POST['items'])),
        ));
       $total3 = $_POST['sub_total'];
    }
}elseif ($_POST['type'] == 'sp2_out') {
    // INTERCHANGE
    $printer->setEmphasis(true);
    $printer->text("Biaya Stripping Interchange \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['sp2_out_items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['name'],
            number_format($item['total']),
        ));
        $total1 = $item['sub_total'];
    }
}
elseif ($_POST['type'] == 'jasa_bongkar') {
    // INTERCHANGE
    $printer->setEmphasis(true);
    $printer->text("Biaya Stevedoring+LOLO \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['jasa_bongkar_items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['name'],
            number_format($item['total']),
        ));
        $total7 = $item['sub_total'];
    }
}
elseif ($_POST['type'] == 'container_storage') {
    // PENUMPUKAN MASA
    $printer->setEmphasis(true);
    $printer->text("Biaya Perpanjangan Storage \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['container_storage_items'] as $item) {
        $renewal = date_create($item['renewal_date']);
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['container_type'] .'('.$item['days'] .' hari)' ."\n" .'s.d ' .date_format($renewal,"d F Y"),
            "\n".number_format($item['total']),
        ));
        $total = $item['sub_total'];

    }
}elseif ($_POST['type'] == 'sp2_muat_int') {
    //INTECHANGE
    $printer->setEmphasis(true);
    $printer->text("Biaya Interchange Muat \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['sp2_muat_items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['name'],
            "\n".number_format($item['total']),
        ));
        $total4 = $item['sub_total'];

    }
}elseif($_POST['type'] == 'jasa_muat') {
    // STEVEDORING
    $printer->setEmphasis(true);
    $printer->text("Biaya Stevedoring+LOLO \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['container_type'],
            number_format($_POST['sub_total'] / count($_POST['items'])),
        ));
       $total5 = $_POST['sub_total'];
    }
} elseif($_POST['type'] == 'spk_depo_in') {
    // SPK DEPO IN
    $printer->setEmphasis(true);
    $printer->text("Biaya SPK Muat Depo \n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['container_type'],
            number_format($_POST['sub_total'] / count($_POST['items'])),
        ));
       $total6 = $_POST['sub_total'];
    }
} elseif($_POST['type'] == 'sp_bongkar') {
    // SPK DEPO IN
    $printer->setEmphasis(true);
    $printer->text("Jasa Handling Khusus Bongkar\n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['container_type'],
            number_format($_POST['sub_total'] / count($_POST['items'])),
        ));
       $total8 = $_POST['sub_total'];
    }
} elseif($_POST['type'] == 'sp_muat') {
    // SPK DEPO IN
    $printer->setEmphasis(true);
    $printer->text("Jasa Handling Khusus Muat\n");
    $printer->initialize(); // Reset bentuk/jenis teks
    $printer->setFont(Printer::FONT_B);
    $printer->text(buatBaris4Kolom("No Kontainer","Detail", "Total"));
    $printer->text("----------------------------------------------------------\n");
    foreach ($_POST['items'] as $item) {
        $printer->text(buatBaris4Kolom(
            strtoupper($item['container']),
            $item['container_type'],
            number_format($_POST['sub_total'] / count($_POST['items'])),
        ));
       $total9 = $_POST['sub_total'];
    }
}


$sub_total = $total + $total1 + $total2 + $total3 + $total4 + $total5 + $total6 + $total7 + $total8 + $total9;
if ($_POST['ppn'] != 0) {
    $ppn = $sub_total*11/100;
}
$printer->text("----------------------------------------------------------\n");
$printer->setEmphasis(true);
$printer->text(buatBaris4Kolom('TAGIHAN','Rp.'.number_format($sub_total),''));
$printer->initialize(); // Reset bentuk/jenis teks
$printer->setFont(Printer::FONT_B);
$printer->text("----------------------------------------------------------\n");
$printer->setEmphasis(true);
$printer->text(buatBaris4Kolom('PPN 11%', 'Rp.'. number_format($ppn),''));
$printer->text(buatBaris4Kolom('GRAND TOTAL', 'Rp.' .number_format($sub_total + $ppn),''));
$printer->initialize(); // Reset bentuk/jenis teks
$printer->setFont(Printer::FONT_B);
$printer->text("\n");

$printer->text("----------------------------------------------------------\n");
$printer->setFont(Printer::FONT_A);
$printer->text("Metode Pembayaran \n");
$printer->setFont(Printer::FONT_B);
$printer->text("1. Transfer ke Rekening Prima Anugrah Sejahterah Nusantara \n");
// $printer->text(" \n");
$printer->text("- BNI46 nomor rekening 1296368536 \n");
$printer->text("2. Kasir/Teller PT PASN di Kantor Pelabuhan Tanjung Redeb \n");
// $printer->text("Pelabuhan Tanjung Redeb \n");

// Pesan penutup
$printer->initialize();
$printer->setFont(Printer::FONT_A);
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("-----------------------------\n");
$printer->text("MOHON MELAKUKAN PEMBAYARAN\n");
$printer->text("SESUAI TOTAL TAGIHAN\n");
$printer->text("=============================\n");
$printer->setFont(Printer::FONT_B);
$printer->text("Supported by: banuanta.id \n");
$printer->text("Email: info@banuanta.id \n");


$printer->feed(3); // mencetak 3 baris kosong agar terangkat (pemotong kertas saya memiliki jarak 5 baris dari toner)
$printer->close();

//potong kertas
$printer->cut();

/* Close printer */
$printer->close();
