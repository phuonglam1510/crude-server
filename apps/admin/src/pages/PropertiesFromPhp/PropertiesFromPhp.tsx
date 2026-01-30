import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Box, Typography, Button, Alert, Stack, Snackbar } from "@mui/material";
import {
  DataGrid,
  type GridRowSelectionModel,
  type GridRowId,
} from "@mui/x-data-grid";
import SyncIcon from "@mui/icons-material/Sync";
import LoginIcon from "@mui/icons-material/Login";
import LogoutIcon from "@mui/icons-material/Logout";
import VisibilityIcon from "@mui/icons-material/Visibility";
import { useTranslation } from "react-i18next";
import { usePhpPropertiesStore } from "../../stores/phpPropertiesStore";
import { useLanguagePrefix } from "../../hooks/useLanguagePrefix";
import PhpLoginModal from "./PhpLoginModal";

const PropertiesFromPhp: React.FC = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const languagePrefix = useLanguagePrefix();
  const {
    token,
    properties,
    total,
    loading,
    error,
    login,
    fetchProperties,
    syncSelected,
    clearError,
    logout,
  } = usePhpPropertiesStore();

  const [loginOpen, setLoginOpen] = useState(false);
  const [rowSelection, setRowSelection] = useState<GridRowSelectionModel>(
    () => ({
      type: "include",
      ids: new Set<GridRowId>(),
    })
  );
  const [snackbar, setSnackbar] = useState<{
    open: boolean;
    message: string;
    jobId?: string;
  }>({ open: false, message: "" });
  const [paginationModel, setPaginationModel] = useState({
    page: 0,
    pageSize: 10,
  });

  const needsLogin = !token;

  useEffect(() => {
    if (token) {
      fetchProperties(paginationModel.page + 1, paginationModel.pageSize);
    } else {
      setLoginOpen(true);
    }
  }, [token, paginationModel.page, paginationModel.pageSize]);

  const handleLoginSuccess = () => {
    fetchProperties(1, paginationModel.pageSize);
  };

  const handleLogout = () => {
    logout();
    setLoginOpen(true);
    setRowSelection({ type: "include", ids: new Set<GridRowId>() });
  };

  const handleSyncSelected = async () => {
    const ids = Array.from(rowSelection.ids)
      .map(Number)
      .filter((id) => !Number.isNaN(id));
    if (ids.length === 0) {
      setSnackbar({ open: true, message: t("properties.selectAtLeastOne") });
      return;
    }
    const result = await syncSelected(ids);
    if (result.jobId) {
      setSnackbar({
        open: true,
        message: t("properties.syncQueued"),
        jobId: result.jobId,
      });
      setRowSelection({ type: "include", ids: new Set<GridRowId>() });
    } else if (result.error) {
      setSnackbar({ open: true, message: result.error });
    }
  };

  const formatMoney = (value: number | undefined) => {
    if (value == null) return "—";
    return (
      new Intl.NumberFormat("vi-VN", {
        style: "decimal",
        maximumFractionDigits: 0,
      }).format(value) + " ₫"
    );
  };

  const columns = [
    { field: "id", headerName: "ID", width: 80 },
    {
      field: "title",
      headerName: t("properties.title"),
      flex: 1,
      minWidth: 220,
    },
    {
      field: "house_address",
      headerName: t("properties.address"),
      width: 150,
    },
    {
      field: "area",
      headerName: t("properties.area"),
      width: 100,
      type: "number" as const,
    },
    {
      field: "into_money",
      headerName: t("properties.price"),
      width: 140,
      type: "number" as const,
      valueFormatter: (value: number) => formatMoney(value),
    },
    { field: "province", headerName: t("properties.province"), width: 120 },
    { field: "district", headerName: t("properties.district"), width: 120 },
  ];

  const rows: { id: number; [k: string]: unknown }[] =
    properties?.map((p) => ({
      id: p.id,
      title: p.title ?? "—",
      house_address: p.house_address ?? p.house_number ?? "—",
      area: p.area ?? "—",
      into_money: p.into_money,
      province: p.province ?? "—",
      district: p.district ?? "—",
    })) ?? [];

  return (
    <Box>
      <Stack
        direction="row"
        justifyContent="space-between"
        alignItems="center"
        mb={3}
      >
        <Typography variant="h4">{t("properties.titlePage")}</Typography>
        <Stack direction="row" spacing={2}>
          {token && (
            <>
              <Button
                variant="outlined"
                startIcon={<VisibilityIcon />}
                onClick={() => navigate(`${languagePrefix}/sync-jobs`)}
              >
                {t("properties.viewSyncJobs")}
              </Button>
              <Button
                variant="contained"
                startIcon={<SyncIcon />}
                onClick={handleSyncSelected}
                disabled={loading || rowSelection.ids.size === 0}
              >
                {t("properties.syncSelected")} ({rowSelection.ids.size})
              </Button>
              <Button
                variant="outlined"
                startIcon={<LogoutIcon />}
                onClick={handleLogout}
              >
                {t("properties.logout")}
              </Button>
            </>
          )}
        </Stack>
      </Stack>

      {error && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={clearError}>
          {error}
        </Alert>
      )}

      {needsLogin && !loginOpen && (
        <Alert severity="info" sx={{ mb: 2 }}>
          {t("properties.loginRequired")}
          <Button
            size="small"
            startIcon={<LoginIcon />}
            onClick={() => setLoginOpen(true)}
            sx={{ ml: 1 }}
          >
            {t("properties.login")}
          </Button>
        </Alert>
      )}

      {token && (
        <Box sx={{ width: "100%" }}>
          <DataGrid
            rows={rows}
            columns={columns}
            loading={loading}
            checkboxSelection
            rowSelectionModel={rowSelection}
            onRowSelectionModelChange={(model) => {
              setRowSelection({
                type: model.type,
                ids: new Set<GridRowId>(model.ids),
              });
            }}
            paginationModel={paginationModel}
            onPaginationModelChange={setPaginationModel}
            paginationMode="server"
            rowCount={Number(total) || 0}
            pageSizeOptions={[10, 25, 50, 100]}
            initialState={{
              pagination: {
                paginationModel: { page: 0, pageSize: 10 },
              },
            }}
            disableRowSelectionOnClick
            sx={{
              "& .MuiDataGrid-cell:focus": { outline: "none" },
            }}
          />
        </Box>
      )}

      <PhpLoginModal
        open={loginOpen}
        onClose={() => {
          setLoginOpen(false);
          navigate(`${languagePrefix}/`);
        }}
        onSuccess={handleLoginSuccess}
        login={login}
        allowClose={!!token}
      />

      <Snackbar
        open={snackbar.open}
        autoHideDuration={6000}
        onClose={() => setSnackbar((s) => ({ ...s, open: false }))}
        message={snackbar.message}
        action={
          snackbar.jobId ? (
            <Button
              size="small"
              onClick={() => {
                navigate(`${languagePrefix}/sync-jobs`);
                setSnackbar((s) => ({ ...s, open: false }));
              }}
            >
              {t("properties.viewJobs")}
            </Button>
          ) : undefined
        }
      />
    </Box>
  );
};

export default PropertiesFromPhp;
