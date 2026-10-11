<?php

namespace Tests\Unit;

use App\Http\Controllers\SupplierController;
use Tests\TestCase;
use ZipArchive;

class SupplierTemplateGenerationTest extends TestCase
{
    public function test_supplier_template_is_a_valid_xlsx_file(): void
    {
        $response = app(SupplierController::class)->downloadImportTemplate();
        $path = $response->getFile()->getPathname();
        $zip = new ZipArchive();

        $this->assertStringContainsString(
            'template-import-supplier.xlsx',
            (string) $response->headers->get('Content-Disposition')
        );
        $this->assertTrue($zip->open($path) === true);
        $this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));
        $this->assertStringContainsString('nama', $zip->getFromName('xl/worksheets/sheet1.xml'));

        $zip->close();
        @unlink($path);
    }

    public function test_reader_supports_excel_shared_strings_after_template_is_edited(): void
    {
        $response = app(SupplierController::class)->downloadImportTemplate();
        $path = $response->getFile()->getPathname();
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);

        $values = ['nama', 'kode', 'telepon', 'email', 'alamat', 'contact_person', 'telepon_contact_person', 'npwp', 'status', 'Supplier Edit', 'EDIT-01', 'aktif'];
        $shared = '<?xml version="1.0" encoding="UTF-8"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        foreach ($values as $value) $shared .= '<si><t>'.$value.'</t></si>';
        $shared .= '</sst>';
        $sheet = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
            .'<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c><c r="D1" t="s"><v>3</v></c><c r="E1" t="s"><v>4</v></c><c r="F1" t="s"><v>5</v></c><c r="G1" t="s"><v>6</v></c><c r="H1" t="s"><v>7</v></c><c r="I1" t="s"><v>8</v></c></row>'
            .'<row r="2"><c r="A2" t="s"><v>9</v></c><c r="B2" t="s"><v>10</v></c><c r="I2" t="s"><v>11</v></c></row>'
            .'</sheetData></worksheet>';
        $zip->addFromString('xl/sharedStrings.xml', $shared);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();

        $method = new \ReflectionMethod(SupplierController::class, 'readImportRows');
        $rows = $method->invoke(app(SupplierController::class), $path, 'xlsx');

        $this->assertSame('nama', $rows[0][0]);
        $this->assertSame('Supplier Edit', $rows[1][0]);
        @unlink($path);
    }

    public function test_reader_detects_semicolon_csv_from_local_excel(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'supplier-csv-');
        file_put_contents($path, "nama;kode;status\r\nSupplier CSV;CSV-01;aktif\r\n");
        $method = new \ReflectionMethod(SupplierController::class, 'readImportRows');
        $rows = $method->invoke(app(SupplierController::class), $path, 'csv');

        $this->assertSame(['nama', 'kode', 'status'], $rows[0]);
        $this->assertSame('Supplier CSV', $rows[1][0]);
        @unlink($path);
    }
}
