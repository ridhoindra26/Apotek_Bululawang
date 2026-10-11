// window.testSwal = () => {
//   Swal.fire({
//     title: 'Test Swal',
//     text: 'Kalau ini tampilnya jelek, berarti CSS masih belum masuk.',
//     icon: 'info',
//   });
// };
import Swal from "sweetalert2";
window.Swal = Swal;

import "./bootstrap";
import "./page-loader";
import "./edit-day";
import "./destroy-day";
import "./logout";
import "./attendance";
import "./attendance-camera";
import "./attendance-photo";
import "./attendance-minutes";
import "./accountPage";
import "./cabang.index";
import "./pasangan.index";
import "./timeLedger";
import "./ledger-modal";
import "./cashier-documents-list";
import "./cashier-multi-upload";

import "./shortener/index";
import "./shortener/show";

import "./suppliers/index";
import "./goods-receipts/index";
import "./goods-receipts/create";