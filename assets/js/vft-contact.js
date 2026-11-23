/**
 * Form "Liên hệ mua hàng" cho vftfarms — JavaScript thuần.
 * Chuyển từ form báo giá của sanpham.chohaosang.vn (D:\xampp\htdocs\code\assets\js\ocop-cart.js):
 * dữ liệu gửi ngầm vào Google Form, Google Form tự ghi vào Google Sheet đã liên kết.
 *
 * Gắn vào nút bằng thuộc tính:
 *   data-vft-contact='{"id","code","name","spec","unit","priceDisplay"}'   (để trống = form chung)
 *   data-vft-qty="#qty"              (tuỳ chọn) lấy số lượng điền sẵn vào ô Số lượng / Quy cách
 *   data-vft-note="#editdescription" (tuỳ chọn) lấy ghi chú điền sẵn
 *   data-vft-source="product_detail" (tuỳ chọn) nguồn mở form, dùng cho gtag
 */
(function () {
  'use strict';

  // ---------- Google Form (đã liên kết với Google Sheet) — giống sanpham.chohaosang.vn ----------
  var GOOGLE_FORM = {
    formId: '1FAIpQLSfguIveddCVPHBbbKzuY1HNPMk3fKKFvc4B8TPFxZ4VGcVR7A',
    entries: {
      fullName: 'entry.2126311057', // Họ và tên
      phone: 'entry.1577836532', // Số điện thoại / Zalo
      product: 'entry.1504826633', // Sản phẩm cần báo giá
      spec: 'entry.538571996', // Số lượng / Quy cách
      address: 'entry.782966687' // Khu vực nhận hàng / Địa chỉ
    }
  };
  var GOOGLE_FORM_URL = 'https://docs.google.com/forms/d/e/' + GOOGLE_FORM.formId + '/formResponse';

  var config = window.VFT_CONTACT || {};

  // ---------- Tiện ích ----------
  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function trackEvent(name, params) {
    if (typeof window.gtag === 'function') window.gtag('event', name, params || {});
  }

  function parseProduct(raw) {
    if (!raw) return null;
    try {
      var p = JSON.parse(raw);
      return p && p.name ? p : null;
    } catch (e) {
      return null;
    }
  }

  function newCode() {
    return 'VFT-' + String(Date.now()).slice(-6);
  }

  // ---------- Gửi Google Form ----------
  function submitViaHiddenForm(values) {
    return new Promise(function (resolve) {
      var name = 'vft_gform_' + Date.now();
      var iframe = document.createElement('iframe');
      iframe.name = name;
      iframe.style.display = 'none';
      var form = document.createElement('form');
      form.action = GOOGLE_FORM_URL;
      form.method = 'POST';
      form.target = name;
      form.style.display = 'none';
      Object.keys(values).forEach(function (key) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = GOOGLE_FORM.entries[key];
        input.value = values[key];
        form.appendChild(input);
      });
      document.body.appendChild(iframe);
      document.body.appendChild(form);
      iframe.onload = function () { resolve(); };
      form.submit();
      setTimeout(function () {
        form.remove();
        iframe.remove();
        resolve();
      }, 4000);
    });
  }

  // values: {fullName, phone, product, spec, address}
  function sendToGoogleForm(values) {
    if (!window.fetch || !window.URLSearchParams) return submitViaHiddenForm(values);
    var body = new URLSearchParams();
    Object.keys(values).forEach(function (key) {
      body.append(GOOGLE_FORM.entries[key], values[key]);
    });
    return fetch(GOOGLE_FORM_URL, { method: 'POST', mode: 'no-cors', body: body }).catch(function () {
      return submitViaHiddenForm(values);
    });
  }

  // ---------- Modal ----------
  var modal = document.createElement('div');
  modal.className = 'vft-ct';
  modal.hidden = true;
  modal.setAttribute('role', 'dialog');
  modal.setAttribute('aria-modal', 'true');
  modal.setAttribute('aria-labelledby', 'vft-ct-title');

  var state = null;

  function newRow(product, spec) {
    return {
      product: product ? product.name + (product.code ? ' (' + product.code + ')' : '') : '',
      spec: spec || ''
    };
  }

  function field(opts) {
    var control = opts.textarea
      ? '<textarea name="' + opts.name + '" rows="2" placeholder="' + escapeHtml(opts.placeholder) + '">' + escapeHtml(opts.value || '') + '</textarea>'
      : '<input type="' + (opts.type || 'text') + '" name="' + opts.name + '" value="' + escapeHtml(opts.value || '') + '" placeholder="' +
        escapeHtml(opts.placeholder) + '"' + (opts.autocomplete ? ' autocomplete="' + opts.autocomplete + '"' : '') + '>';
    return '<label class="vft-ct-field"><span class="vft-ct-label"><i class="' + opts.icon + '"></i><span>' + opts.label +
      (opts.required ? ' <em>*</em>' : '') + '</span></span>' + control + '</label>';
  }

  function rowHtml(row, i, total) {
    return '<div class="vft-ct-row">' +
      '<div class="vft-ct-row-head"><span class="vft-ct-row-no">' + (i + 1) + '</span><span>Sản phẩm #' + (i + 1) + '</span>' +
      (total > 1 ? '<button type="button" class="vft-ct-row-remove" data-vft-row-remove="' + i + '" title="Xóa mặt hàng này"><i class="fas fa-trash-alt"></i> Xóa</button>' : '') +
      '</div><div class="vft-ct-grid">' +
      '<label class="vft-ct-field"><span class="vft-ct-label">Tên sản phẩm <em>*</em></span>' +
      '<input type="text" data-vft-row-product="' + i + '" value="' + escapeHtml(row.product) + '" placeholder="Ví dụ: Gạo ST25, Rau cải..."></label>' +
      '<label class="vft-ct-field"><span class="vft-ct-label">Số lượng / Quy cách <small>Không bắt buộc</small></span>' +
      '<input type="text" data-vft-row-spec="' + i + '" value="' + escapeHtml(row.spec) + '" placeholder="Ví dụ: 2kg, 10 hộp..."></label>' +
      '</div></div>';
  }

  function hotlineBtn() {
    if (!config.hotlineTel) return '';
    return '<a href="tel:' + escapeHtml(config.hotlineTel) + '" class="vft-ct-btn vft-ct-btn-primary" data-vft-track-call>' +
      '<i class="fas fa-phone-alt"></i> Gọi Hotline: ' + escapeHtml(config.hotlineDisplay || config.hotlineTel) + '</a>';
  }

  function render() {
    var s = state;
    var p = s.product;
    var html = '<div class="vft-ct-backdrop" data-vft-close></div><div class="vft-ct-box">' +
      '<div class="vft-ct-head"><div>' +
      '<h3 id="vft-ct-title">' + (p ? 'Liên hệ mua hàng: ' + escapeHtml(p.name) : 'Liên Hệ Mua Hàng &amp; Báo Giá') + '</h3>' +
      (p && p.code ? '<p>Mã SP: <strong>' + escapeHtml(p.code) + '</strong>' + (p.priceDisplay ? ' &nbsp;·&nbsp; Giá: <strong>' + escapeHtml(p.priceDisplay) + '</strong>' : '') + '</p>' : '') +
      '</div><button type="button" class="vft-ct-close" data-vft-close aria-label="Đóng"' + (s.submitting ? ' disabled' : '') + '>&times;</button></div>' +
      '<div class="vft-ct-body">';

    if (config.hotlineTel && !s.success) {
      html += '<div class="vft-ct-card"><p class="vft-ct-overline">Cách 1: Gọi trực tiếp để được tư vấn ngay</p>' + hotlineBtn() + '</div>' +
        '<p class="vft-ct-overline">Cách 2: Điền thông tin – chúng tôi sẽ gọi lại cho bạn</p>';
    }

    if (s.success) {
      html += '<div class="vft-ct-success">' +
        '<div class="vft-ct-success-icon"><i class="fas fa-check"></i></div>' +
        '<span class="vft-ct-code">Mã yêu cầu: ' + escapeHtml(s.code) + '</span>' +
        '<h4>Gửi yêu cầu thành công!</h4>' +
        '<p>' + escapeHtml(config.brand || 'Chúng tôi') + ' đã nhận được thông tin và sẽ liên hệ số điện thoại <strong>' +
        escapeHtml(s.phone) + '</strong> trong thời gian sớm nhất.</p>' +
        '<div class="vft-ct-actions">' + hotlineBtn() +
        '<button type="button" class="vft-ct-btn vft-ct-btn-outline" data-vft-reset><i class="fas fa-redo"></i> Gửi yêu cầu khác</button></div></div>';
    } else {
      html += '<form class="vft-ct-form" novalidate><div class="vft-ct-grid">' +
        field({ name: 'fullName', label: 'Họ và tên', icon: 'fas fa-user', required: true, placeholder: 'Ví dụ: Nguyễn Văn A', value: s.values.fullName, autocomplete: 'name' }) +
        field({ name: 'phone', type: 'tel', label: 'Số điện thoại / Zalo', icon: 'fas fa-phone-alt', required: true, placeholder: 'Số điện thoại nhận tư vấn', value: s.values.phone, autocomplete: 'tel' }) +
        '</div><div class="vft-ct-rows"><div class="vft-ct-rows-head"><span class="vft-ct-label"><i class="fas fa-shopping-basket"></i><span>Sản phẩm cần mua <em>*</em></span></span>' +
        '<small>' + (s.rows.length > 1 ? s.rows.length + ' mặt hàng' : 'Có thể thêm nhiều sản phẩm') + '</small></div>' +
        s.rows.map(function (row, i) { return rowHtml(row, i, s.rows.length); }).join('') +
        '<button type="button" class="vft-ct-btn vft-ct-btn-soft" data-vft-row-add><i class="fas fa-plus"></i> Thêm sản phẩm khác</button></div>' +
        field({ name: 'address', label: 'Khu vực / Địa chỉ nhận hàng', icon: 'fas fa-map-marker-alt', placeholder: 'Ví dụ: Thủ Đức, TP. Hồ Chí Minh', value: s.values.address, autocomplete: 'street-address' }) +
        field({ name: 'notes', textarea: true, label: 'Ghi chú thêm (nếu có)', icon: 'fas fa-pen', placeholder: 'Thời gian giao, yêu cầu đóng gói...', value: s.values.notes }) +
        '<p class="vft-ct-error" data-vft-error hidden></p>' +
        '<button type="submit" class="vft-ct-btn vft-ct-btn-primary vft-ct-btn-block"' + (s.submitting ? ' disabled' : '') + '>' +
        (s.submitting ? '<i class="fas fa-spinner fa-spin"></i> Đang gửi...' : '<i class="fas fa-paper-plane"></i> Gửi yêu cầu liên hệ') +
        '</button></form>';
    }
    html += '</div></div>';
    modal.innerHTML = html;
  }

  // Giữ lại dữ liệu đang nhập trước khi vẽ lại form
  function syncInputs() {
    var form = modal.querySelector('form');
    if (!form) return;
    ['fullName', 'phone', 'address', 'notes'].forEach(function (name) {
      if (form.elements[name]) state.values[name] = form.elements[name].value;
    });
    state.rows.forEach(function (row, i) {
      var pInput = modal.querySelector('[data-vft-row-product="' + i + '"]');
      var sInput = modal.querySelector('[data-vft-row-spec="' + i + '"]');
      if (pInput) row.product = pInput.value;
      if (sInput) row.spec = sInput.value;
    });
  }

  function showError(message) {
    var box = modal.querySelector('[data-vft-error]');
    if (!box) return;
    box.textContent = message;
    box.hidden = !message;
  }

  function open(product, opts) {
    opts = opts || {};
    state = {
      product: product || null,
      source: opts.source || 'contact_button',
      rows: [newRow(product, opts.spec)],
      values: { notes: opts.note || '' },
      submitting: false,
      success: false,
      code: '',
      phone: ''
    };
    render();
    if (!modal.parentNode) document.body.appendChild(modal);
    modal.hidden = false;
    document.body.classList.add('vft-ct-locked');
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { modal.classList.add('is-visible'); });
    });
    var first = modal.querySelector('input[name="fullName"]');
    if (first) setTimeout(function () { first.focus(); }, 50);
    trackEvent('open_quote_modal', { productId: product ? product.id : '', sourceLocation: state.source });
  }

  function close() {
    if (!state || state.submitting) return;
    modal.classList.remove('is-visible');
    document.body.classList.remove('vft-ct-locked');
    setTimeout(function () {
      if (!modal.classList.contains('is-visible')) modal.hidden = true;
    }, 250);
  }

  modal.addEventListener('click', function (e) {
    if (e.target.closest('[data-vft-close]')) {
      close();
    } else if (e.target.closest('[data-vft-track-call]')) {
      trackEvent('click_call_hotline', { sourceLocation: 'contact_modal' });
    } else if (e.target.closest('[data-vft-row-add]')) {
      syncInputs();
      state.rows.push(newRow(null));
      render();
      var last = modal.querySelector('[data-vft-row-product="' + (state.rows.length - 1) + '"]');
      if (last) last.focus();
    } else if (e.target.closest('[data-vft-row-remove]')) {
      syncInputs();
      state.rows.splice(parseInt(e.target.closest('[data-vft-row-remove]').getAttribute('data-vft-row-remove'), 10), 1);
      if (state.rows.length === 0) state.rows.push(newRow(null));
      render();
    } else if (e.target.closest('[data-vft-reset]')) {
      state.success = false;
      state.values = {};
      state.rows = [newRow(null)];
      render();
    }
  });

  modal.addEventListener('submit', function (e) {
    e.preventDefault();
    syncInputs();
    var v = state.values;
    var fullName = (v.fullName || '').trim();
    var phone = (v.phone || '').trim();
    showError('');
    if (!fullName) return showError('Vui lòng nhập Họ và tên.');
    if (phone.replace(/\D/g, '').length < 8) return showError('Vui lòng nhập Số điện thoại hoặc Zalo chính xác.');
    var rows = state.rows.filter(function (r) { return r.product.trim(); });
    if (rows.length === 0) return showError('Vui lòng nhập ít nhất một sản phẩm cần mua.');

    var productText = rows.length === 1
      ? rows[0].product.trim()
      : rows.map(function (r, i) { return (i + 1) + '. ' + r.product.trim(); }).join(' | ');
    var specText = rows.length === 1
      ? (rows[0].spec.trim() || 'Tư vấn theo nhu cầu')
      : rows.map(function (r, i) { return (i + 1) + '. ' + (r.spec.trim() || 'Tư vấn theo nhu cầu'); }).join(' | ');
    var notes = (v.notes || '').trim();
    state.code = newCode();
    specText = 'Mã yêu cầu: ' + state.code + ' | ' + specText + (notes ? ' | Ghi chú: ' + notes : '') +
      (config.brand ? ' | Nguồn: ' + config.brand : '');

    state.submitting = true;
    render();
    sendToGoogleForm({
      fullName: fullName,
      phone: phone,
      product: productText,
      spec: specText,
      address: (v.address || '').trim() || 'Chưa ghi rõ'
    }).then(function () {
      trackEvent('submit_quote_request', {
        quoteCode: state.code,
        productId: state.product ? state.product.id : '',
        productName: productText,
        sourceLocation: state.source
      });
      state.submitting = false;
      state.success = true;
      state.phone = phone;
      render();
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) close();
  });

  // ---------- Nút trong trang ----------
  function readValue(selector) {
    if (!selector) return '';
    var input = document.querySelector(selector);
    return input ? String(input.value).trim() : '';
  }

  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-vft-contact]');
    if (!el || modal.contains(el)) return;
    e.preventDefault();
    var product = parseProduct(el.getAttribute('data-vft-contact'));
    var qty = readValue(el.getAttribute('data-vft-qty'));
    var spec = '';
    if (product) {
      spec = qty && parseFloat(qty.replace(',', '.')) > 0 ? qty + (product.unit ? ' ' + product.unit : '') : '';
      if (product.spec) spec = spec ? spec + ' - ' + product.spec : product.spec;
    }
    var opts = { source: el.getAttribute('data-vft-source') || 'contact_button', spec: spec, note: readValue(el.getAttribute('data-vft-note')) };

    // Nút nằm trong popup Bootstrap (xem nhanh sản phẩm): đóng popup trước để không bị giữ focus
    var bsModal = el.closest('.modal');
    if (bsModal && bsModal.classList.contains('show') && window.jQuery && window.jQuery.fn.modal) {
      var opened = false;
      var go = function () {
        if (opened) return;
        opened = true;
        open(product, opts);
      };
      window.jQuery(bsModal).one('hidden.bs.modal', go).modal('hide');
      setTimeout(go, 600); // phòng khi popup không phát sự kiện hidden
    } else {
      open(product, opts);
    }
  });

  window.VftContact = { open: open, close: close };
})();
