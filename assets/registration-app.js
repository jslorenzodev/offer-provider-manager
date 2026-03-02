/* global opmRegistration, wp */
(function () {
  if (!window.wp || !wp.element) {
    return;
  }

  const { createElement: h, useMemo, useState } = wp.element;

  function Field({ id, label, type, value, onChange, autoComplete }) {
    return h("div", { className: "space-y-1" },
      h("label", { htmlFor: id, className: "block text-sm font-medium text-slate-700" }, label),
      h("input", {
        id,
        type,
        value,
        onChange,
        autoComplete,
        required: true,
        className: "block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200"
      })
    );
  }

  function Alert({ kind, children }) {
    const base = "rounded-xl px-4 py-3 text-sm border";
    const cls = kind === "error"
      ? base + " bg-rose-50 border-rose-200 text-rose-800"
      : base + " bg-emerald-50 border-emerald-200 text-emerald-800";
    return h("div", { className: cls, role: kind === "error" ? "alert" : "status" }, children);
  }

  function RegistrationApp() {
    const cfg = window.opmRegistration || {};
    const companyName = cfg.companyName || "";
    const token = cfg.token || "";

    const [username, setUsername] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [password2, setPassword2] = useState("");
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState("");
    const [success, setSuccess] = useState("");

    const canSubmit = useMemo(() => {
      return username.trim() && email.trim() && password && password2 && !submitting;
    }, [username, email, password, password2, submitting]);

    async function submit(e) {
      e.preventDefault();
      setError("");
      setSuccess("");

      if (!canSubmit) return;

      setSubmitting(true);
      try {
        const payload = {
          token,
          username,
          email,
          password,
          password2,
          opm_confirm_url: "",          // honeypot (keep empty)
          opm_reg_nonce: cfg.tokenNonce // token-specific nonce
        };

        const res = await fetch(cfg.restUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": cfg.wpRestNonce
          },
          body: JSON.stringify(payload),
          credentials: "same-origin"
        });

        const data = await res.json();

        if (!res.ok || !data || !data.success) {
          setError((data && data.message) ? data.message : "Registration failed. Please try again.");
          return;
        }

        setSuccess(data.message || "Registration successful! You can now log in.");
        setUsername("");
        setEmail("");
        setPassword("");
        setPassword2("");
      } catch (err) {
        setError("Registration failed. Please try again.");
      } finally {
        setSubmitting(false);
      }
    }

    return h("div", { className: "min-h-screen bg-slate-50" },
      h("div", { className: "mx-auto max-w-md px-4 py-10" },
        h("div", { className: "rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 sm:p-8" },

          h("div", { className: "inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700" },
            "Invitation-only"
          ),

          h("h1", { className: "mt-4 text-2xl font-semibold tracking-tight text-slate-900" }, "Create your account"),
          h("p", { className: "mt-1 text-sm text-slate-600" },
            companyName ? ("You are registering for " + companyName + ".") : "Complete the form below."
          ),

          error ? h("div", { className: "mt-4" }, h(Alert, { kind: "error" }, error)) : null,
          success ? h("div", { className: "mt-4" }, h(Alert, { kind: "success" }, success)) : null,

          h("form", { className: "mt-6 space-y-4", onSubmit: submit, noValidate: true },

            h(Field, {
              id: "opm_username",
              label: "Username",
              type: "text",
              value: username,
              onChange: (e) => setUsername(e.target.value),
              autoComplete: "username"
            }),

            h(Field, {
              id: "opm_email",
              label: "Email",
              type: "email",
              value: email,
              onChange: (e) => setEmail(e.target.value),
              autoComplete: "email"
            }),

            h("div", { className: "grid grid-cols-1 gap-4 sm:grid-cols-2" },
              h(Field, {
                id: "opm_password",
                label: "Password",
                type: "password",
                value: password,
                onChange: (e) => setPassword(e.target.value),
                autoComplete: "new-password"
              }),
              h(Field, {
                id: "opm_password2",
                label: "Confirm",
                type: "password",
                value: password2,
                onChange: (e) => setPassword2(e.target.value),
                autoComplete: "new-password"
              })
            ),

            h("button", {
              type: "submit",
              disabled: !canSubmit,
              className:
                "mt-2 inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold " +
                "bg-slate-900 text-white shadow-sm hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
            }, submitting ? "Creating account..." : "Create account"),

            h("p", { className: "text-center text-sm text-slate-600" },
              "Already have an account? ",
              h("a", { className: "font-semibold text-slate-900 underline underline-offset-4", href: (cfg.loginUrl || "/wp-login.php") }, "Log in")
            )
          )
        )
      )
    );
  }

  function boot() {
    const root = document.getElementById("opm-registration-root");
    if (!root) return;
    if (wp.element && wp.element.render) {
      wp.element.render(h(RegistrationApp), root);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
