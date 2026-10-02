# SMARTLIFE — PAYMENT SANDBOX STATUS

Ngày: 2026-10-01

## Trạng thái

Gateway: Stripe anonymous sandbox (Stripe CLI)  
Sandbox: Đã tạo tạm; account ID `acct_1ULn1dCbrVn0E8Zc`; hết hạn 2026-10-08 nếu không claim  
Account: Sandbox-only, chưa claim thành tài khoản Stripe đầy đủ  
Email verification: Chưa xác minh; email provisioning là `checkout-test@example.test` và không thể nhận thư  
KYC: Chưa yêu cầu cho anonymous sandbox. Stripe yêu cầu business verification/KYC khi kích hoạt dịch vụ live.  
Credentials: Test publishable/restricted keys đã cấp và lưu trong Stripe CLI profile; chỉ key prefix được biết ở đây (`pk_test_…`, `rkcs_test_…`). Full secrets/claim URL không ghi vào workspace hoặc báo cáo.  
WooCommerce connection: Chưa kết nối. Stripe plugin chưa cài.  
Payment test: Chưa có Stripe payment. Order #115 là checkout BACS demo/offline, đã cancelled để hoàn stock; order #74 vẫn pending.

## Kết quả thực tế

- Temporary Stripe sandbox created through the official Stripe CLI anonymous sandbox flow.
- Test credentials were generated and persisted by Stripe CLI outside the project workspace.
- No production credentials, real card, real bank account, live payment, or KYC information were used.
- `woocommerce_sepay_settings.enabled` vẫn `no`; BACS/Cheque/COD demo settings không đổi trong nhiệm vụ này.
- Chưa cài hoặc bật payment plugin mới.
- Stripe sandbox test keys are **not connected** to WooCommerce. The existing offline BACS test is not a Stripe transaction.

## Cần làm tiếp

1. Nếu muốn giữ sandbox sau 2026-10-08, tạo/claim sandbox bằng email thật mà người dùng kiểm soát; email `.test` hiện dùng không thể verify.
2. Mở Stripe CLI profile trên chính máy đã provision và thực hiện claim thủ công; không gửi secret hoặc claim URL qua chat.
3. Cài extension Stripe chính thức nếu được yêu cầu, sau đó vào WooCommerce → Settings → Payments → Stripe → Manage → Settings → Configure connection → Test.
4. Xác nhận Test mode trước khi dùng test values. Không bật Live mode và không nhập Live keys.

## Không thay đổi dữ liệu bài

- 40 products preserved.
- 40 unique SKUs preserved.
- 6 parent / 22 child categories preserved.
- 8 global attributes và assignment được giữ nguyên.
- Backup `data_shop-before-smartlife-20261001.sql` được giữ nguyên.
- Không có production payment transaction.
- Order #74 còn pending; order #115 còn trong DB ở trạng thái cancelled; tồn kho test đã phục hồi.
- Website vẫn ở local WooCommerce demo mode, không có gateway Stripe/PayPal kết nối.

## Tài liệu chính thức

- [Stripe CLI sandbox](https://docs.stripe.com/cli/sandbox)
- [Stripe sandbox](https://docs.stripe.com/sandboxes)
- [PayPal sandbox overview](https://developer.paypal.com/sandbox-testing/overview/)
- [PayPal sandbox accounts](https://developer.paypal.com/sandbox-testing/accounts/)
- [SePay WooCommerce integration](https://docs.sepay.vn/woocommerce.html)
