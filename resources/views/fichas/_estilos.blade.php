<style>
    .fx-wrap { font-family: 'Poppins', sans-serif; color: #1B2B5A; padding: 18px 6px; max-width: 1150px; margin: 0 auto; }
    .fx-volver { font-size: 13.5px; color: #2563EB; text-decoration: none; }
    .fx-title { font-size: 21px; font-weight: 600; margin: 8px 0 2px; }
    .fx-sub { font-size: 13px; color: #6E7A96; font-weight: 300; margin-bottom: 16px; }
    .fx-sub a { color: #0d8a4f; text-decoration: none; }

    .fx-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 991px) { .fx-kpis { grid-template-columns: repeat(2, 1fr); } }
    .fx-kpi { background: #fff; border: 1px solid #E7EAF2; border-radius: 14px; padding: 13px 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); }
    .fx-kpi .l { font-size: 10.5px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: #6E7A96; }
    .fx-kpi .v { font-size: 20px; font-weight: 700; }
    .fx-kpi.deuda .v { color: #b4552d; }
    .fx-kpi.ok .v { color: #0d8a4f; }

    .fx-card { background: #fff; border: 1px solid #E7EAF2; border-radius: 16px; box-shadow: 0 10px 30px rgba(27,43,90,.06); overflow: hidden; margin-bottom: 16px; }
    .fx-card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 13px 16px 11px; border-bottom: 1px solid #F1F4F9; }
    .fx-card-head h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #47536F; margin: 0; }
    .fx-card-head h3 i { color: #2563EB; }
    .fx-link { border: 1.5px solid #E7EAF2; color: #47536F; background: #fff; border-radius: 999px; padding: 5px 14px; font-size: 12px; font-weight: 500; text-decoration: none; }
    .fx-link:hover { border-color: #2563EB; color: #2563EB; text-decoration: none; }

    .fx-table { width: 100%; border-collapse: collapse; }
    .fx-table th { background: #F8FAFC; border-bottom: 1px solid #E7EAF2; color: #6E7A96; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; padding: 9px 14px; text-align: left; }
    .fx-table td { padding: 10px 14px; border-bottom: 1px solid #F1F4F9; font-size: 13px; }
    .fx-table tr:hover td { background: #F8FAFC; }
    .fx-table .der { text-align: right; }
    .fx-table a { color: #2563EB; text-decoration: none; font-weight: 600; }

    .fx-chip { display: inline-block; border-radius: 999px; font-size: 10.5px; font-weight: 600; padding: 2px 10px; background: #E0F2FE; color: #1B2B5A; }
    .fx-chip.ok { background: #DCFCE7; color: #166534; }
    .fx-chip.pend { background: #FEF3C7; color: #92400E; }
    .fx-chip.mal { background: #FEE2E2; color: #991B1B; }
    .fx-plata { font-size: 11px; color: #166534; }
    .fx-vacio { text-align: center; color: #94A3B8; font-size: 12.5px; font-weight: 300; padding: 24px; }
</style>
