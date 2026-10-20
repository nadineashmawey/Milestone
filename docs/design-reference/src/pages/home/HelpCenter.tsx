import { useEffect, useMemo, useState } from "react";
import { HomeIcon } from "./HomeIcon";
import { homeFaqs } from "./HomePage";
import "./home.css";

type HelpCenterProps = { onBack: () => void; onLogin: () => void };

const topics: { name: string; questions: [string, string][] }[] = [
  { name: "Accounts and sign in", questions: [
    ["Which email should I use?", "Your university email. Student, professor and TA accounts must use the university domain, and professor and TA roles are verified by an administrator."],
    ["I forgot my password.", "Choose “Forgot password” on the sign-in page. A reset link is sent to your university email and expires after a short time."],
    ["Why can’t I change my email or student ID?", "They identify you across courses, so only an administrator can change them. You can request a correction from your profile."],
  ] },
  { name: "Teams", questions: homeFaqs.filter(([q]) => q.includes("team") || q.includes("deadline")) },
  { name: "Milestones and grades", questions: homeFaqs.filter(([q]) => q.includes("milestone") || q.includes("grade")) },
  { name: "Independent projects", questions: [
    homeFaqs[0],
    ["How do people join my independent project?", "Invite them by email or username, or share the project code or link. You approve each join request, up to 10 members."],
  ] },
];

const categories = ["Can’t sign in", "Team or membership", "Submission or upload", "Grades or feedback", "Something else"];

type FormState = { name: string; email: string; category: string; description: string };
type Errors = Partial<Record<keyof FormState, string>>;

function validate(form: FormState): Errors {
  const errors: Errors = {};
  if (!form.name.trim()) errors.name = "Enter your name.";
  if (!form.email.trim()) errors.email = "Enter your email so we can reply.";
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) errors.email = "Enter a valid email address, like name@university.edu.";
  if (!form.category) errors.category = "Choose what the problem is about.";
  if (form.description.trim().length < 20) errors.description = `Describe the problem in at least 20 characters (${form.description.trim().length}/20).`;
  return errors;
}

export function HelpCenter({ onBack, onLogin }: HelpCenterProps) {
  const [query, setQuery] = useState("");
  const [form, setForm] = useState<FormState>({ name: "", email: "", category: "", description: "" });
  const [errors, setErrors] = useState<Errors>({});
  const [status, setStatus] = useState<"idle" | "sending" | "sent" | "failed">("idle");
  const [simulateFailure, setSimulateFailure] = useState(false);
  const [reference, setReference] = useState("");

  useEffect(() => { window.scrollTo(0, 0); }, []);

  const results = useMemo(() => {
    const q = query.trim().toLowerCase();
    return topics
      .map((topic) => ({ ...topic, questions: topic.questions.filter(([question, answer]) => !q || `${question} ${answer}`.toLowerCase().includes(q)) }))
      .filter((topic) => topic.questions.length);
  }, [query]);

  const update = (field: keyof FormState, value: string) => {
    setForm({ ...form, [field]: value });
    if (errors[field]) setErrors({ ...errors, [field]: undefined });
  };

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    const found = validate(form);
    setErrors(found);
    if (Object.keys(found).length) {
      document.getElementById(`hp-help-${Object.keys(found)[0]}`)?.focus();
      return;
    }
    setStatus("sending");
    window.setTimeout(() => {
      if (simulateFailure) { setStatus("failed"); return; }
      setReference(`MS-${Math.floor(1000 + Math.random() * 9000)}`);
      setStatus("sent");
    }, 900);
  };

  const field = (key: keyof FormState) => ({
    id: `hp-help-${key}`,
    value: form[key],
    "aria-invalid": Boolean(errors[key]),
    "aria-describedby": errors[key] ? `hp-help-${key}-error` : undefined,
    disabled: status === "sending",
  });

  return <div className="hp hp-help">
    <header className="hp-nav scrolled">
      <div className="hp-nav-inner">
        <button type="button" className="hp-logo" onClick={onBack} aria-label="Back to Milestone home"><img src="/milestone-logo.png" alt="Milestone"/></button>
        <div className="hp-nav-actions always">
          <button type="button" className="hp-btn ghost" onClick={onBack}><HomeIcon name="back" size={18}/> Home</button>
          <button type="button" className="hp-btn primary" onClick={onLogin}>Sign in</button>
        </div>
      </div>
    </header>

    <main id="main">
      <section className="hp-help-hero">
        <p className="hp-eyebrow">Help center</p>
        <h1>How can we help?</h1>
        <label className="hp-search">
          <HomeIcon name="search" size={20}/>
          <span className="hp-sr">Search help articles</span>
          <input type="search" placeholder="Search, for example “leave team” or “resubmit”" value={query} onChange={(e) => setQuery(e.target.value)}/>
        </label>
      </section>

      <div className="hp-help-layout">
        <section className="hp-help-topics" aria-label="Help articles">
          {results.length ? results.map((topic) => <div className="hp-help-topic" key={topic.name}>
            <h2>{topic.name}</h2>
            {topic.questions.map(([question, answer]) => <details key={question}>
              <summary>{question}</summary>
              <p>{answer}</p>
            </details>)}
          </div>) : <div className="hp-help-empty" role="status">
            <strong>No articles match “{query}”.</strong>
            <p>Try a shorter word, or describe the problem in the form and we’ll reply by email.</p>
            <button type="button" className="hp-btn secondary" onClick={() => setQuery("")}>Clear search</button>
          </div>}
        </section>

        <aside className="hp-report" aria-labelledby="hp-report-title">
          {status === "sent" ? <div className="hp-report-done" role="status">
            <span className="hp-report-icon ok"><HomeIcon name="check" size={22}/></span>
            <h2 id="hp-report-title">Report sent</h2>
            <p>Thanks, {form.name.split(" ")[0]}. We’ll reply to <strong>{form.email}</strong>. Your reference is <strong>{reference}</strong>.</p>
            <button type="button" className="hp-btn secondary" onClick={() => { setForm({ name: "", email: "", category: "", description: "" }); setStatus("idle"); }}>Report another problem</button>
          </div> : <form onSubmit={submit} noValidate>
            <span className="hp-report-icon"><HomeIcon name="message" size={20}/></span>
            <h2 id="hp-report-title">Report a problem</h2>
            <p className="hp-report-intro">Tell us what went wrong. Don’t include passwords or grades.</p>

            {status === "failed" && <div className="hp-alert" role="alert">
              <HomeIcon name="alert" size={18}/>
              <div><strong>We couldn’t send your report.</strong><span>Your details are still here. Check your connection and try again.</span></div>
            </div>}

            <label htmlFor="hp-help-name">Name</label>
            <input {...field("name")} autoComplete="name" onChange={(e) => update("name", e.target.value)}/>
            {errors.name && <small className="hp-field-error" id="hp-help-name-error">{errors.name}</small>}

            <label htmlFor="hp-help-email">Email</label>
            <input {...field("email")} type="email" autoComplete="email" onChange={(e) => update("email", e.target.value)}/>
            {errors.email && <small className="hp-field-error" id="hp-help-email-error">{errors.email}</small>}

            <label htmlFor="hp-help-category">What is it about?</label>
            <select {...field("category")} onChange={(e) => update("category", e.target.value)}>
              <option value="">Choose one</option>
              {categories.map((category) => <option key={category}>{category}</option>)}
            </select>
            {errors.category && <small className="hp-field-error" id="hp-help-category-error">{errors.category}</small>}

            <label htmlFor="hp-help-description">What happened?</label>
            <textarea {...field("description")} rows={5} placeholder="What were you trying to do, and what did you see instead?" onChange={(e) => update("description", e.target.value)}/>
            {errors.description && <small className="hp-field-error" id="hp-help-description-error">{errors.description}</small>}

            <button type="submit" className="hp-btn primary large full" disabled={status === "sending"}>{status === "sending" ? <><span className="hp-spinner" aria-hidden="true"/>Sending…</> : status === "failed" ? "Try again" : "Send report"}</button>
            <label className="hp-simulate"><input type="checkbox" checked={simulateFailure} onChange={(e) => setSimulateFailure(e.target.checked)}/> Prototype only: simulate a failed send</label>
          </form>}
        </aside>
      </div>
    </main>
  </div>;
}
