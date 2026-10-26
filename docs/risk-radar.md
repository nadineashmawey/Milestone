# Risk Radar

Route: `/professor/risk-radar`, named `professor.risk-radar.index`.

This screen is an ML-ready presentation of a sample prediction contract from `ProfessorRiskRadarController`. Each team has an ID, risk level, progress and activity signals, workload, inactive days, estimated delay, recommended action, and member activity. The selected team propagates between chart bubbles, team quick selectors, contribution dropdown, forecast card, and what-if simulator. The report export downloads sample CSV.

All values are illustrative and hard-coded. There is no trained model, confidence score, live activity provider, or current course data. The what-if calculation illustrates arithmetic on sample delay; it is not model inference and does not change the actual deadline. Contribution is an activity index, not work share or a grade. Do not present model accuracy or calibrated probabilities until the model supplies them.

For ML integration, replace the fixture with an authenticated service and keep the UI contract explicit: team and milestone IDs, risk category, signal values with measurement windows and timestamps, forecast horizon, explanation grounded in supplied signals, recommended actions, model/version and generated time. Show confidence only after calibration. Authorization must scope results to the professor's course; handle unavailable/stale predictions and omit sensitive student attributes from model inputs unless explicitly approved.
