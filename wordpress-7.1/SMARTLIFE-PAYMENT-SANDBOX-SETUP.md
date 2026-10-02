# SMARTLIFE — PAYMENT SANDBOX SETUP

## 1. Gateway

- Tên gateway: Stripe (temporary anonymous sandbox đã được cấp qua Stripe CLI)
- Website chính thức: [Stripe](https://stripe.com/)
- Sandbox/Test mode: Có. Stripe sandbox không kết nối mạng lưới thẻ/ngân hàng thật và test transactions không chuyển tiền thật.
- Sandbox account ID: `acct_1ULn1dCbrVn0E8Zc`
- Sandbox hết hạn: `2026-10-08` (7 ngày kể từ lúc tạo, theo phản hồi Stripe CLI).
- Trạng thái tài khoản:
  - [ ] Chưa đăng ký tài khoản Stripe đầy đủ
  - [ ] Đã đăng ký tài khoản Stripe đầy đủ
  - [ ] Đã xác minh email
  - [ ] Đang chờ xác minh email
  - [x] Đã tạo anonymous sandbox và có test credentials
  - [ ] Đã kết nối với WooCommerce
- Stripe test keys được Stripe CLI lưu trong CLI profile cục bộ; không ghi secret hoặc claim URL vào workspace. Secret key bắt đầu `rkcs_test_`, publishable key bắt đầu `pk_test_`; giá trị đầy đủ đã được redacted.
- Email nhận dạng khi provision là `checkout-test@example.test`, đây là địa chỉ reserved/test, không nhận được email. Không thể dùng nó để claim/verify sandbox.
- Chưa cài Stripe gateway plugin, chưa kết nối WooCommerce và không thay đổi payment settings trong nhiệm vụ này.

### Gateway đang có

- `SePay Gateway` 1.1.23 đã cài nhưng đang disabled. Source/plugin docs không có test/sandbox mode; thiết lập yêu cầu tài khoản SePay, ủy quyền, chọn tài khoản ngân hàng và tạo webhook. Không dùng SePay cho sandbox này.
- BACS, Cheque và COD là gateways built-in của WooCommerce, hiện có tên/mô tả demo local; chúng không phải cổng test-card.
- Không có Stripe hoặc PayPal gateway plugin cài trong WordPress.

### Các lựa chọn theo tài liệu chính thức

| Gateway | Sandbox/test | Account/credentials | Identity/email requirements |
|---|---|---|---|
| Stripe | Có isolated sandbox, test keys, test values | Test account/sandbox ID, publishable key, restricted/secret test key; webhook signing secret nếu cấu hình webhook | Anonymous sandbox CLI tạo được mà không login. Claim sandbox cần thao tác browser; live activation yêu cầu business verification/KYC. |
| PayPal Payments | Có PayPal Developer Sandbox, business và personal test accounts | Sandbox business account, buyer/personal account, REST Client ID và Secret khi dùng Manual Connect | Developer signup yêu cầu email verification; sandbox account được tạo trong Developer Dashboard. Tài liệu sandbox không nêu yêu cầu KYC; live onboarding có thể yêu cầu xác minh riêng. |
| SePay | Không tìm thấy test/sandbox mode trong plugin hiện cài | Tài khoản SePay, liên kết ngân hàng, webhook/API key | Tích hợp dùng tài khoản/bank thật để nhận và đối soát chuyển khoản; không phù hợp mục tiêu sandbox. |

## 2. Thông tin cần lấy

- Merchant/Test account ID: Stripe sandbox `acct_1ULn1dCbrVn0E8Zc` (temporary).
- Client ID / App ID: Chưa có OAuth app/Stripe WooCommerce connection.
- Public key: Đã có `pk_test_...` trong Stripe CLI profile; không lưu giá trị vào workspace.
- Secret key: Đã có `rkcs_test_...` trong Stripe CLI profile; không lưu/hiển thị giá trị.
- Test API key: Có trong Stripe CLI profile; test-only.
- Webhook secret: Chưa tạo webhook endpoint, chưa có.
- Claim URL: Stripe CLI đã cấp và lưu trong profile; không in vào báo cáo hoặc ghi vào workspace. Dùng lệnh CLI claim để mở sau khi có email hợp lệ.

## 3. Các bước kết nối WooCommerce sau này

Stripe plugin chưa được cài hoặc kết nối. Khi người dùng quyết định kết nối, chỉ cài extension chính thức [Stripe for WooCommerce](https://woocommerce.com/products/stripe/) và giữ nguyên test mode:

1. Plugins → Add New → tìm “Stripe” by WooCommerce/Stripe → Install → Activate.
2. WooCommerce → Settings → Payments → Stripe → Manage → Settings.
3. Trong Account details, chọn Configure connection → Test → Create or connect a test account.
4. Connect riêng tài khoản sandbox; xác nhận trạng thái Test.
5. Bật Enable test mode trong Stripe settings và Save changes.
6. Dùng test values chính thức trong [Stripe testing docs](https://docs.stripe.com/testing); không dùng thẻ thật. Không nhập production keys.

Tài liệu WooCommerce nói kết nối Stripe test mode riêng với live mode. Tài khoản test-only có thể dùng luồng Create or connect a test account. Trước khi cấu hình, cần thay anonymous sandbox sắp hết hạn bằng sandbox được claim từ một email mà người dùng kiểm soát.

### PayPal thay thế nếu bài yêu cầu PayPal

1. Đăng nhập hoặc tạo developer account tại [developer.paypal.com](https://developer.paypal.com/).
2. Xác minh email theo yêu cầu PayPal.
3. Developer Dashboard → Sandbox → Accounts: dùng/tạo một Business sandbox và một Personal sandbox (buyer).
4. Lấy REST app Sandbox Client ID/Secret trong Developer Dashboard.
5. Chỉ sau khi xác nhận lựa chọn PayPal mới cài plugin chính thức [WooCommerce PayPal Payments](https://woocommerce.com/document/woocommerce-paypal-payments/) và bật Sandbox Mode.

Không cài PayPal plugin trong nhiệm vụ này vì mục tiêu là chuẩn bị credentials, chưa phải kết nối gateway.

## 4. Kiểm tra trước khi test

- [x] Gateway có sandbox/test isolation.
- [x] Chưa dùng production credential.
- [x] Không dùng thẻ thật.
- [x] Guest checkout vẫn bật.
- [x] BACS demo hiện có được giữ nguyên.
- [x] 40 sản phẩm không thay đổi.
- [x] Tồn kho hiện đã được hoàn nguyên sau checkout sandbox #115.
- [x] Order #74 vẫn pending và không bị thay đổi.
- [x] Order #115 giữ record ở trạng thái cancelled; không xóa.
- [x] Không tạo sản phẩm mới.
- [x] Backup trước thay đổi vẫn còn ở `C:/laragon/backups/data_shop-before-smartlife-20261001.sql`.
- [ ] WooCommerce gateway sandbox được kết nối (chưa làm).

## 5. Test payment (chưa chạy qua Stripe)

Stripe sandbox đã có test credentials trong CLI profile nhưng chưa có Stripe gateway plugin kết nối với WooCommerce. Checkout #115 trước đó được submit bằng BACS demo/offline, không phải Stripe. Chưa có Stripe transaction/order.

Khi kết nối gateway:

1. Bật Test mode trước khi thử.
2. Add một sản phẩm giá thấp vào cart và checkout guest.
3. Chọn Stripe Test, dùng test data lấy trực tiếp từ [Stripe testing docs](https://docs.stripe.com/testing); không tự tạo số thẻ.
4. Submit một lần, ghi order number/status/payment result và stock trước/sau.
5. Không chuyển mode sang live, không điền production credentials.

## Nguồn chính thức

- [Stripe sandbox](https://docs.stripe.com/sandboxes)
- [Stripe CLI sandbox](https://docs.stripe.com/cli/sandbox)
- [Stripe API keys](https://docs.stripe.com/keys)
- [WooCommerce Stripe testing](https://woocommerce.com/document/stripe/customer-experience/testing/)
- [WooCommerce Stripe connection](https://woocommerce.com/document/stripe/setup-and-configuration/connecting-to-stripe/)
- [PayPal sandbox overview](https://developer.paypal.com/sandbox-testing/overview/)
- [PayPal sandbox accounts](https://developer.paypal.com/sandbox-testing/accounts/)
- [WooCommerce PayPal Payments startup guide](https://woocommerce.com/document/woocommerce-paypal-payments/paypal-payments-startup-guide/)
- [SePay WooCommerce integration](https://docs.sepay.vn/woocommerce.html)
