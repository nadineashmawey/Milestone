import { useEffect, useState } from "react";
import { HomeIcon } from "./HomeIcon";
import "./home.css";

type HomePageProps = { onLogin: () => void; onStart: () => void; onHelp: () => void };

const navLinks = [
  { href: "#how", label: "How it works" },
  { href: "#types", label: "Project types" },
  { href: "#roles", label: "Roles" },
  { href: "#faq", label: "FAQ" },
];

const steps = [
  { title: "Set the structure", text: "The professor publishes the project once: milestones, deliverable types, team size limits, the team-choosing deadline and the grading total.", detail: "Professor · Create project" },
  { title: "Work visibly", text: "Teams form before the deadline, then plan on a four-column board. Every status change, file version and blocker is recorded with who and when.", detail: "Students · Tasks, files, team" },
  { title: "Review with context", text: "The team leader submits. TAs comment or review within their permissions, and only the professor scores the rubric and releases the grade.", detail: "TA and professor · Reviews" },
];

const projectTypes = {
  academic: {
    name: "Academic project",
    tagline: "Run by a course. Supervised, submitted and graded.",
    rows: [
      ["Created by", "The professor, inside a course and semester"],
      ["Team leader", "Assigned by the professor or TA (no voting)"],
      ["Team size", "Minimum and maximum set by the professor"],
      ["Submissions", "Leader submits each milestone; late work is flagged, not penalised"],
      ["Grades", "Professor only, per team, locked once finalised"],
      ["After the course", "Read-only 30 days after the semester ends"],
    ],
  },
  independent: {
    name: "Independent project",
    tagline: "Started by any student. Same tools, no grading.",
    rows: [
      ["Created by", "Any student, with no approval needed"],
      ["Team leader", "The creator, who can transfer leadership"],
      ["Team size", "Up to 10 members, by invite, link or code"],
      ["Submissions", "None. Teams plan and deliver on their own terms"],
      ["Grades", "None. No professor or TA involvement"],
      ["After the course", "Kept for as long as the team needs it"],
    ],
  },
};

const roles = [
  { name: "Professor", tone: "blue", icon: "folder", points: ["Publish projects and milestones", "Lock teams and assign leaders", "Score rubrics and release grades"] },
  { name: "Teaching assistant", tone: "violet", icon: "check", points: ["Review assigned submissions", "Write feedback within permissions", "Report project issues"] },
  { name: "Student", tone: "green", icon: "users", points: ["Join a team before the deadline", "Track tasks and deadlines", "Run independent projects too"] },
  { name: "Administrator", tone: "yellow", icon: "grid", points: ["Import users from CSV", "Manage courses and terms", "Keep an immutable audit log"] },
];

export const homeFaqs: [string, string][] = [
  ["Can students create their own projects?", "Yes. Independent projects use the same task, team, file and calendar tools, without professor supervision, submissions or grades. Any student can start one and invite up to 10 members."],
  ["Who can submit academic milestones?", "Only the team leader assigned by the professor or TA. They can edit and resubmit before the deadline, and every version keeps its submitter and timestamp."],
  ["What happens at the team-choosing deadline?", "Teams lock and pending requests are cancelled. Unassigned students are placed automatically or by the professor, depending on the mode the professor chose."],
  ["Can I leave my team?", "Not on your own. You send a leave request to the professor, and you stay on the team until they approve it."],
  ["Does the AI grade students?", "No. Risk and contribution signals are evidence for the professor, clearly labelled as simulated in this prototype. Grades are always set and released by the professor."],
  ["Can a finalised grade be changed?", "No. Once the professor finalises a grade it is locked. Disputes are handled by email with the professor, outside Milestone."],
];

function HeroPreview() {
  const tasks = [
    { title: "API contract", who: "AH", status: "In review", tone: "violet" },
    { title: "Database schema", who: "MM", status: "In progress", tone: "blue" },
    { title: "Use case diagrams", who: "NS", status: "Done", tone: "green" },
  ];
  return <div className="hp-preview" role="img" aria-label="Preview of a team workspace showing the M2 milestone, three tasks and a submission receipt">
    <div className="hp-window" aria-hidden="true">
      <div className="hp-window-bar"><span/><span/><span/><em>milestone.university.edu</em></div>
      <div className="hp-window-body">
        <div className="hp-win-head">
          <div><small>Software Engineering Capstone</small><strong>Team 1</strong></div>
          <span className="hp-pill green">On track</span>
        </div>
        <div className="hp-win-milestone">
          <div className="hp-win-milestone-top"><span className="hp-tag">M2</span><strong>Design document</strong><small>Due Oct 18 · 5:00 PM</small></div>
          <div className="hp-win-items">
            <span className="done">SRS update</span><span className="done">Diagrams</span><span className="done">Prototype link</span><span>Reflection</span>
          </div>
          <div className="hp-bar"><span style={{ width: "75%" }}/></div>
          <small className="hp-win-meta">3 of 4 deliverables ready</small>
        </div>
        <ul className="hp-win-tasks">
          {tasks.map((task) => <li key={task.title}><b className={`hp-face ${task.tone}`}>{task.who}</b><span>{task.title}</span><em className={`hp-pill ${task.tone}`}>{task.status}</em></li>)}
        </ul>
      </div>
    </div>
    <div className="hp-receipt" aria-hidden="true">
      <span className="hp-receipt-icon"><HomeIcon name="check" size={16}/></span>
      <div><strong>M1 proposal · version 3</strong><small>Submitted by Aya Hassan · Sep 19, 4:52 PM</small></div>
    </div>
  </div>;
}

export function HomePage({ onLogin, onStart, onHelp }: HomePageProps) {
  const [menuOpen, setMenuOpen] = useState(false);
  const [openFaq, setOpenFaq] = useState(0);
  const [activeType, setActiveType] = useState<"academic" | "independent">("academic");
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    if (!menuOpen) return;
    const close = (event: KeyboardEvent) => { if (event.key === "Escape") setMenuOpen(false); };
    window.addEventListener("keydown", close);
    return () => window.removeEventListener("keydown", close);
  }, [menuOpen]);

  return <div className="hp">
    <a className="hp-skip" href="#main">Skip to content</a>
    <header className={`hp-nav ${scrolled ? "scrolled" : ""}`}>
      <div className="hp-nav-inner">
        <a className="hp-logo" href="#top" aria-label="Milestone home"><img src="/milestone-logo.png" alt="Milestone"/></a>
        <nav className="hp-nav-links" aria-label="Main">
          {navLinks.map((link) => <a key={link.href} href={link.href}>{link.label}</a>)}
          <button type="button" onClick={onHelp}>Help</button>
        </nav>
        <div className="hp-nav-actions">
          <button type="button" className="hp-btn ghost" onClick={onLogin}>Sign in</button>
          <button type="button" className="hp-btn primary" onClick={onStart}>Get started</button>
          <button type="button" className="hp-menu-toggle" aria-label={menuOpen ? "Close menu" : "Open menu"} aria-expanded={menuOpen} aria-controls="hp-mobile-menu" onClick={() => setMenuOpen(!menuOpen)}>
            <span/><span/>
          </button>
        </div>
      </div>
      {menuOpen && <div className="hp-mobile-menu" id="hp-mobile-menu">
        <nav aria-label="Mobile">
          {navLinks.map((link) => <a key={link.href} href={link.href} onClick={() => setMenuOpen(false)}>{link.label}</a>)}
          <button type="button" onClick={() => { setMenuOpen(false); onHelp(); }}>Help</button>
        </nav>
        <div>
          <button type="button" className="hp-btn secondary" onClick={onLogin}>Sign in</button>
          <button type="button" className="hp-btn primary" onClick={onStart}>Create account</button>
        </div>
      </div>}
    </header>

    <main id="main">
      <section className="hp-hero" id="top">
        <div className="hp-hero-copy">
          <p className="hp-kicker"><span/>For university project courses</p>
          <h1>Group projects that don’t fall apart.</h1>
          <p className="hp-lede">Milestone brings professors, teaching assistants and student teams into one workspace, from forming teams to the final grade. Everyone sees the same deadlines, and nobody has to ask who did what.</p>
          <div className="hp-hero-actions">
            <button type="button" className="hp-btn primary large" onClick={onStart}>Start with Milestone <HomeIcon name="arrow" size={18}/></button>
            <a className="hp-btn secondary large" href="#how">See how it works</a>
          </div>
          <p className="hp-note">Frontend prototype. Email, GitHub evidence and risk predictions are simulated.</p>
        </div>
        <HeroPreview/>
      </section>

      <section className="hp-facts" aria-label="Milestone at a glance">
        <dl>
          <div><dt>4</dt><dd>roles with their own permissions</dd></div>
          <div><dt>2</dt><dd>project types, one set of tools</dd></div>
          <div><dt>Every</dt><dd>submission version kept with its author</dd></div>
          <div><dt>0</dt><dd>grades decided by the AI</dd></div>
        </dl>
      </section>

      <section className="hp-section" id="how">
        <div className="hp-heading">
          <p className="hp-eyebrow">How it works</p>
          <h2>Structure the project once. Keep every team aligned.</h2>
        </div>
        <ol className="hp-steps">
          {steps.map((step, i) => <li key={step.title}>
            <span className="hp-step-num">{String(i + 1).padStart(2, "0")}</span>
            <h3>{step.title}</h3>
            <p>{step.text}</p>
            <small>{step.detail}</small>
          </li>)}
        </ol>
      </section>

      <section className="hp-section hp-types" id="types">
        <div className="hp-heading split">
          <div>
            <p className="hp-eyebrow">Project types</p>
            <h2>Coursework or side project, same workspace.</h2>
          </div>
          <p>Academic projects follow the course rules. Independent projects give students the same board, files and calendar for anything else they build together.</p>
        </div>
        <div className="hp-type-switch" role="tablist" aria-label="Project type">
          {(["academic", "independent"] as const).map((key) => <button key={key} type="button" role="tab" id={`hp-tab-${key}`} aria-selected={activeType === key} aria-controls="hp-type-panel-mobile" onClick={() => setActiveType(key)}>{projectTypes[key].name}</button>)}
        </div>
        <div className="hp-type-grid">
          {(["academic", "independent"] as const).map((key) => {
            const type = projectTypes[key];
            return <article key={key} className={`hp-type ${key} ${activeType === key ? "active" : ""}`} id={activeType === key ? "hp-type-panel-mobile" : undefined} aria-labelledby={`hp-type-title-${key}`}>
              <header>
                <span className="hp-type-badge">{key === "academic" ? "Academic" : "Independent"}</span>
                <h3 id={`hp-type-title-${key}`}>{type.name}</h3>
                <p>{type.tagline}</p>
              </header>
              <dl>{type.rows.map(([label, value]) => <div key={label}><dt>{label}</dt><dd>{value}</dd></div>)}</dl>
            </article>;
          })}
        </div>
      </section>

      <section className="hp-section" id="roles">
        <div className="hp-heading">
          <p className="hp-eyebrow">Roles</p>
          <h2>Clear permissions for everyone involved.</h2>
        </div>
        <div className="hp-roles">
          {roles.map((role) => <article key={role.name} className={`hp-role ${role.tone}`}>
            <span className="hp-role-icon"><HomeIcon name={role.icon}/></span>
            <h3>{role.name}</h3>
            <ul>{role.points.map((point) => <li key={point}>{point}</li>)}</ul>
          </article>)}
        </div>
      </section>

      <section className="hp-signal" aria-labelledby="hp-signal-title">
        <div className="hp-signal-copy">
          <p className="hp-eyebrow light">Evidence, not guesswork</p>
          <h2 id="hp-signal-title">Know which team needs attention before a deadline slips.</h2>
          <p>Milestone combines task progress, activity, submissions and contribution evidence into signals that explain themselves. They inform the professor, they never decide a grade.</p>
          <ul>
            <li>Every risk level comes with its reason</li>
            <li>Contribution summaries are evidence, not scores</li>
            <li>Deadline, inactivity and workload signals in one place</li>
          </ul>
        </div>
        <div className="hp-signal-card" role="img" aria-label="Example risk signal: Team 3 may miss M2 because four tasks are open and activity slowed for three days">
          <div className="hp-signal-top"><span className="hp-pill yellow">Medium risk</span><small>Simulated</small></div>
          <strong>Team 3 may miss M2</strong>
          <p>Four tasks are still open and activity has slowed for three days.</p>
          <div className="hp-signal-bars" aria-hidden="true">
            <div><span>Tasks done</span><i><b style={{ width: "42%" }}/></i><em>42%</em></div>
            <div><span>Activity, 7 days</span><i><b className="violet" style={{ width: "28%" }}/></i><em>Low</em></div>
            <div><span>Time left</span><i><b className="coral" style={{ width: "18%" }}/></i><em>3 days</em></div>
          </div>
          <footer>Suggested: confirm owners for the remaining tasks today.</footer>
        </div>
      </section>

      <section className="hp-section hp-faq" id="faq">
        <div className="hp-heading split">
          <div>
            <p className="hp-eyebrow">FAQ</p>
            <h2>Questions people ask first.</h2>
          </div>
          <p>Can’t find your answer? The <button type="button" className="hp-inline-link" onClick={onHelp}>Help center</button> has more, plus a form to report a problem.</p>
        </div>
        <div className="hp-faq-list">
          {homeFaqs.map(([question, answer], i) => {
            const open = openFaq === i;
            return <div className={`hp-faq-item ${open ? "open" : ""}`} key={question}>
              <h3><button type="button" aria-expanded={open} aria-controls={`hp-faq-${i}`} id={`hp-faq-q-${i}`} onClick={() => setOpenFaq(open ? -1 : i)}>
                <span>{question}</span><i aria-hidden="true"/>
              </button></h3>
              <div className="hp-faq-answer" id={`hp-faq-${i}`} role="region" aria-labelledby={`hp-faq-q-${i}`} hidden={!open}><p>{answer}</p></div>
            </div>;
          })}
        </div>
      </section>

      <section className="hp-cta">
        <h2>Bring your next group project into one place.</h2>
        <p>Use your university email. Professors and TAs are verified before their role is active.</p>
        <div>
          <button type="button" className="hp-btn primary large" onClick={onStart}>Create an account</button>
          <button type="button" className="hp-btn secondary large" onClick={onLogin}>Sign in</button>
        </div>
      </section>
    </main>

    <footer className="hp-footer">
      <div className="hp-footer-brand">
        <span className="hp-logo"><img src="/milestone-logo.png" alt="Milestone"/></span>
        <p>Clear projects. Stronger teams. Better outcomes.</p>
      </div>
      <nav aria-label="Footer">
        <div><h4>Product</h4><a href="#how">How it works</a><a href="#types">Project types</a><a href="#roles">Roles</a></div>
        <div><h4>Support</h4><button type="button" onClick={onHelp}>Help center</button><button type="button" onClick={onHelp}>Report a problem</button><a href="#faq">FAQ</a></div>
        <div><h4>Account</h4><button type="button" onClick={onLogin}>Sign in</button><button type="button" onClick={onStart}>Create account</button></div>
      </nav>
      <small>© 2026 Milestone · Software Engineering 26F, Misr International University · Frontend prototype</small>
    </footer>
  </div>;
}
