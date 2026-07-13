/**
 * SocialAuth Connect — Admin JS.
 *
 * Handles copy-to-clipboard and test connection.
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    // ── Copy-to-clipboard ──────────────────────────────────────────────
    document.querySelectorAll(".socialauth-copy-btn").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var targetId = this.getAttribute("data-copy-target");
        var target = document.getElementById(targetId);
        if (!target) return;

        var text = target.textContent || target.value;
        navigator.clipboard.writeText(text).then(function () {
          var original = btn.textContent;
          btn.textContent = "Copied!";
          btn.classList.add("socialauth-copied");
          setTimeout(function () {
            btn.textContent = original;
            btn.classList.remove("socialauth-copied");
          }, 2000);
        });
      });
    });

    // ── Test Connection ────────────────────────────────────────────────
    var testBtn = document.getElementById("socialauth-test-fb-connection");
    if (testBtn) {
      testBtn.addEventListener("click", function () {
        var result = document.getElementById("socialauth-test-result");
        var clientId = document.querySelector(
          'input[name="socialauth_facebook_settings[client_id]"]'
        );
        var clientSecret = document.querySelector(
          'input[name="socialauth_facebook_settings[client_secret]"]'
        );

        if (!clientId || !clientSecret) {
          showResult(result, "error", "App ID and App Secret fields not found.");
          return;
        }

        if (!clientId.value || !clientSecret.value) {
          showResult(
            result,
            "error",
            "Please enter both App ID and App Secret before testing."
          );
          return;
        }

        testBtn.disabled = true;
        testBtn.textContent = "Testing…";
        showResult(result, "info", "Verifying Facebook App credentials…");

        var data = new FormData();
        data.append("action", "socialauth_test_facebook");
        data.append("nonce", socialauthAdmin.nonce);
        data.append("client_id", clientId.value);
        data.append("client_secret", clientSecret.value);

        fetch(socialauthAdmin.ajaxUrl, {
          method: "POST",
          credentials: "same-origin",
          body: data,
        })
          .then(function (r) {
            return r.json();
          })
          .then(function (res) {
            if (res.success && res.data.valid) {
              showSuccess(result, res.data);
            } else {
              showResult(result, "error", res.data.message);
            }
          })
          .catch(function () {
            showResult(result, "error", "Network error. Please try again.");
          })
          .finally(function () {
            testBtn.disabled = false;
            testBtn.textContent = "Test Connection";
          });
      });
    }

    function showResult(el, type, msg) {
      if (!el) return;
      el.className = "socialauth-test-result socialauth-test-" + type;
      el.textContent = msg;
      el.style.display = "block";
    }

    function showSuccess(el, data) {
      if (!el) return;
      el.textContent = "";
      el.className = "socialauth-test-result socialauth-test-success";

      var line1 = document.createTextNode("✓ App credentials valid!");
      el.appendChild(line1);

      if (data.app_name) {
        var appSpan = document.createElement("strong");
        appSpan.textContent = " App: " + data.app_name;
        el.appendChild(appSpan);
      }

      var br1 = document.createElement("br");
      el.appendChild(br1);

      var warnLine = document.createElement("strong");
      warnLine.textContent = "Important: ";
      el.appendChild(warnLine);
      el.appendChild(document.createTextNode(
        "Ensure this exact URL is in \"Valid OAuth Redirect URIs\":"
      ));

      var br2 = document.createElement("br");
      el.appendChild(br2);

      var codeEl = document.createElement("code");
      codeEl.textContent = data.redirect_uri;
      el.appendChild(codeEl);

      el.style.display = "block";
    }
  });
})();
