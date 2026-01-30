import React, { useEffect, useState } from "react";
import {
  Box,
  Typography,
  Alert,
  Card,
  CardContent,
  Stack,
  Chip,
  Button,
  IconButton,
  Tooltip,
  Snackbar,
} from "@mui/material";
import { DataGrid, GridColDef } from "@mui/x-data-grid";
import RefreshIcon from "@mui/icons-material/Refresh";
import ReplayIcon from "@mui/icons-material/Replay";
import apiClient from "../../config/api";

interface SyncJob {
  id: string;
  type: string;
  status: string;
  total_items: number;
  success_count: number;
  failed_count: number;
  retry_count: number;
  created_at: string;
  started_at: string | null;
  completed_at: string | null;
  error?: string;
}

const SyncJobsPage: React.FC = () => {
  const [jobs, setJobs] = useState<SyncJob[]>([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [queueStats, setQueueStats] = useState<{
    waiting: number;
    active: number;
    completed: number;
    failed: number;
  } | null>(null);
  const [retryingId, setRetryingId] = useState<string | null>(null);
  const [snackbar, setSnackbar] = useState<{
    open: boolean;
    message: string;
    severity: "success" | "error";
  }>({ open: false, message: "", severity: "success" });

  const fetchJobs = async () => {
    setLoading(true);
    setError(null);
    try {
      const [jobsRes, statsRes] = await Promise.all([
        apiClient.get<{ jobs: SyncJob[]; total: number }>("/api/sync/jobs", {
          params: { limit: 50, offset: 0 },
        }),
        apiClient.get<{
          waiting: number;
          active: number;
          completed: number;
          failed: number;
        }>("/api/sync/queue/stats"),
      ]);
      setJobs(jobsRes.data.jobs ?? []);
      setTotal(jobsRes.data.total ?? 0);
      setQueueStats(statsRes.data);
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string } };
        message?: string;
      };
      setError(
        err.response?.data?.message ?? err.message ?? "Failed to load jobs"
      );
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchJobs();
  }, []);

  const handleRetry = async (jobId: string) => {
    setRetryingId(jobId);
    try {
      await apiClient.post<{ message: string; job_id: string }>(
        `/api/sync/jobs/${jobId}/retry`
      );
      setSnackbar({
        open: true,
        message: "Job queued for retry",
        severity: "success",
      });
      await fetchJobs();
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string } };
        message?: string;
      };
      setSnackbar({
        open: true,
        message:
          err.response?.data?.message ?? err.message ?? "Failed to retry job",
        severity: "error",
      });
    } finally {
      setRetryingId(null);
    }
  };

  const columns: GridColDef[] = [
    { field: "id", headerName: "Job ID", width: 280 },
    { field: "type", headerName: "Type", width: 140 },
    {
      field: "status",
      headerName: "Status",
      width: 120,
      renderCell: (params) => {
        const status = params.value as string;
        const color =
          status === "success"
            ? "success"
            : status === "failed"
              ? "error"
              : status === "processing"
                ? "info"
                : "default";
        return <Chip label={status} color={color} size="small" />;
      },
    },
    { field: "total_items", headerName: "Total", width: 80, type: "number" },
    {
      field: "success_count",
      headerName: "Success",
      width: 80,
      type: "number",
    },
    { field: "failed_count", headerName: "Failed", width: 80, type: "number" },
    {
      field: "created_at",
      headerName: "Created",
      width: 170,
      valueFormatter: (value) =>
        value ? new Date(value as string).toLocaleString() : "—",
    },
    {
      field: "completed_at",
      headerName: "Completed",
      width: 170,
      valueFormatter: (value) =>
        value ? new Date(value as string).toLocaleString() : "—",
    },
    {
      field: "error",
      headerName: "Error",
      flex: 1,
      minWidth: 200,
      renderCell: (params) => {
        const msg = params.value as string | undefined;
        if (!msg) return "—";
        const truncated = msg.length > 80 ? `${msg.slice(0, 80)}…` : msg;
        return (
          <Tooltip title={msg} placement="top-start">
            <Typography
              variant="body2"
              sx={{
                color: "error.main",
                overflow: "hidden",
                textOverflow: "ellipsis",
                maxWidth: 400,
              }}
            >
              {truncated}
            </Typography>
          </Tooltip>
        );
      },
    },
    {
      field: "actions",
      headerName: "Actions",
      width: 100,
      sortable: false,
      filterable: false,
      renderCell: (params) => {
        const job = params.row as SyncJob;
        const canRetry = job.status !== "success";
        const isRetrying = retryingId === job.id;
        return canRetry ? (
          <Tooltip title="Retry job">
            <span>
              <IconButton
                size="small"
                onClick={(e) => {
                  e.stopPropagation();
                  handleRetry(job.id);
                }}
                disabled={isRetrying}
                aria-label="Retry job"
              >
                <ReplayIcon fontSize="small" />
              </IconButton>
            </span>
          </Tooltip>
        ) : null;
      },
    },
  ];

  return (
    <Box>
      <Stack
        direction="row"
        justifyContent="space-between"
        alignItems="center"
        mb={3}
      >
        <Typography variant="h4">Sync Jobs</Typography>
        <Button
          startIcon={<RefreshIcon />}
          onClick={fetchJobs}
          disabled={loading}
        >
          Refresh
        </Button>
      </Stack>

      {queueStats && (
        <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
          <Card variant="outlined" sx={{ minWidth: 120 }}>
            <CardContent>
              <Typography color="textSecondary">Waiting</Typography>
              <Typography variant="h5">{queueStats.waiting}</Typography>
            </CardContent>
          </Card>
          <Card variant="outlined" sx={{ minWidth: 120 }}>
            <CardContent>
              <Typography color="textSecondary">Active</Typography>
              <Typography variant="h5">{queueStats.active}</Typography>
            </CardContent>
          </Card>
          <Card variant="outlined" sx={{ minWidth: 120 }}>
            <CardContent>
              <Typography color="textSecondary">Completed</Typography>
              <Typography variant="h5">{queueStats.completed}</Typography>
            </CardContent>
          </Card>
          <Card variant="outlined" sx={{ minWidth: 120 }}>
            <CardContent>
              <Typography color="textSecondary">Failed</Typography>
              <Typography variant="h5">{queueStats.failed}</Typography>
            </CardContent>
          </Card>
        </Stack>
      )}

      {error && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={() => setError(null)}>
          {error}
        </Alert>
      )}

      <Box sx={{ height: 500, width: "100%" }}>
        <DataGrid
          rows={jobs}
          columns={columns}
          loading={loading}
          getRowId={(row) => row.id}
          initialState={{
            pagination: { paginationModel: { pageSize: 10 } },
          }}
          pageSizeOptions={[10, 25, 50]}
        />
      </Box>

      <Snackbar
        open={snackbar.open}
        autoHideDuration={6000}
        onClose={() => setSnackbar((s) => ({ ...s, open: false }))}
        anchorOrigin={{ vertical: "bottom", horizontal: "center" }}
      >
        <Alert
          severity={snackbar.severity}
          onClose={() => setSnackbar((s) => ({ ...s, open: false }))}
          variant="filled"
        >
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
};

export default SyncJobsPage;
