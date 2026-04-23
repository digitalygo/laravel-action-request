# Envai Code of Conduct

Envai is a proprietary platform maintained by the internal product and engineering teams with the support of Opencode, our LLM-powered development assistance program. This Code of Conduct sets expectations for everyone contributing to the codebase, infrastructure, documentation, and related discussions, whether interacting directly with teammates or via Opencode sessions.

## 1. Purpose and Scope

- Applies to all project spaces: private repositories, issue trackers, internal chats, design documents, Opencode prompts/sessions, build systems, and release channels.
- Covers all team members, contractors, and any authorized collaborators granted access to Envai resources.
- Supplements, but does not replace, the company’s employee handbook and security policies. When in doubt, follow the most restrictive guidance.

## 2. Expected Behavior

Everyone is responsible for cultivating a respectful, secure, and productive environment:

1. **Respect and Inclusion**
   - Communicate clearly and professionally; challenge ideas, not people.
   - Use inclusive language and be mindful of different backgrounds, roles, and time zones.
   - Attribute work accurately and credit collaborators, including outputs generated with Opencode.

2. **Collaboration and Accountability**
   - Share context proactively, document decisions, and follow established workflows (Action-First architecture, 90%+ coverage, etc.).
   - Own mistakes openly, resolve issues quickly, and support teammates during incidents or high-priority efforts.
   - Keep Opencode prompts and responses tied to the relevant tasks so that reviews remain audit-friendly.

3. **Security and Confidentiality**
   - Treat all project data, credentials, and model artifacts as confidential trade secrets.
   - Review outputs from Opencode or any automation before merging; ensure no sensitive data is leaked in prompts, logs, or generated files.
   - Follow least-privilege practices and immediately report suspected breaches or data exposure.

## 3. Unacceptable Behavior

The following actions are prohibited and may result in loss of repository access, disciplinary action, or legal consequences:

- Harassment, discrimination, disrespectful language, or personal attacks.
- Sharing proprietary information (code, prompts, model weights, datasets, credentials, analytics, financial data, etc.) outside approved channels.
- Bypassing review, testing, or deployment policies; tampering with CI/CD, audit logs, or coverage reports.
- Uploading malicious content, intentionally introducing security vulnerabilities, or misusing Opencode/LLM tooling to skirt policy.
- Submitting work obtained from third parties or public sources without verifying licensing compatibility and manager approval.

## 4. Reporting Concerns

- **Immediate risks** (security incidents, harassment, data exposure): contact the Security or People Ops lead via the established incident hotline or Slack bridge.
- **General concerns** (policy confusion, repeated workflow violations, suspicious behavior): notify your manager or the Engineering Director.
- **Opencode misuse**: escalate to the AI Governance contact or Platform Lead.

Provide as much detail as possible (who/what/when/where) so the response team can investigate quickly. All reports are handled confidentially and shared only with those who must act on them.

## 5. Enforcement

- Reported incidents will be reviewed promptly by the appropriate leadership group (People Ops, Engineering, Security, Legal).
- Actions may include clarification of expectations, mentorship, temporary suspension of access, formal warnings, or termination of collaboration.
- Retaliation against anyone who raises a concern in good faith is strictly prohibited. Retaliatory behavior will be treated as a separate violation.

## 6. Workflow & Git Practices

- Follow the Envai Gitflow model at all times: create topic branches from `main` using the `feature/*`, `fix/*`, `refactor/*`, or `chore/*` prefixes as appropriate. Never push directly to protected branches.
- Open a Pull Request for **every** change, no matter how small. Reference the provided PR template, keep commits focused, and request review from the designated maintainers.
- Ensure that Action-First workflow requirements, automated tests, linting, and coverage thresholds are satisfied before requesting review. CI failures must be resolved prior to merge.

## 7. Continuous Improvement

This Code of Conduct is a living document. Suggestions for improvement should be directed to the Engineering Director or Project Maintainer. Updates will be communicated through the usual internal channels and recorded in the repository.

By participating in Envai development, you agree to uphold these standards and protect the integrity of our product, our team, and our customers.
