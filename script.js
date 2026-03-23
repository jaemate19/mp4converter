// DOM REFERENCES
const dropzone     = document.getElementById("dropzone");
const fileInput    = document.getElementById("fileInput");
const selectBtn    = document.getElementById("selectBtn");
const convertBtn   = document.getElementById("convertBtn");
const fileNameEl   = document.getElementById("fileName");
const loader       = document.getElementById("loader");
const errorMsg     = document.getElementById("errorMsg");
const downloadLink = document.getElementById("downloadLink");

// EVENT BINDINGS
selectBtn.addEventListener("click", () => fileInput.click());

fileInput.addEventListener("change", e => {
  handleFile(e.target.files[0]);
});

dropzone.addEventListener("dragover", e => {
  e.preventDefault();
  dropzone.classList.add("dropzone--active");
});

dropzone.addEventListener("dragleave", () => {
  dropzone.classList.remove("dropzone--active");
});

dropzone.addEventListener("drop", e => {
  e.preventDefault();
  dropzone.classList.remove("dropzone--active");
  handleFile(e.dataTransfer.files[0]);
});

// APP STATE
let appState = {
  file: null,
  status: "idle", // idle | ready | uploading | done | error
  jobId: null     // Store the job ID for polling
};

// STATE MANAGEMENT
function setState(newState) 
{
  appState = { ...appState, ...newState };
  document.body.dataset.state = appState.status;
  render();
}

// UI RENDER
function render() 
{
  fileNameEl.textContent = appState.file ? appState.file.name : "";
  errorMsg.textContent = "";

  loader.hidden = appState.status !== "uploading";
  convertBtn.hidden = appState.status !== "ready";
  downloadLink.hidden = appState.status !== "done";
}

// FILE VALIDATION
function validateFile(file) 
{
  if (!file) return "No file selected";
  if (file.type !== "video/mp4") return "Only MP4 files allowed";
  if (file.size > 10_000_000) return "File exceeds 10MB limit";
  return null;
}

// FILE HANDLING
function handleFile(file) 
{
  const error = validateFile(file);
  if (error) 
  {
    errorMsg.textContent = error;
    setState({ status: "error", file: null });
    return;
  }
  setState({ file, status: "ready" });
}

// UPLOAD & CONVERT
convertBtn.addEventListener("click", async () => 
{
  if (!appState.file) return;

  setState({ status: "uploading" });

  const formData = new FormData();
  formData.append("upload", appState.file);

  try 
  {
    const response = await fetch("convert.php", {  // Make sure this matches your filename
      method: "POST",
      body: formData
    });

    const result = await response.json();

    if (result.status !== "success") {
      throw new Error(result.message || "Upload failed");
    }

    // Store job ID and start polling
    appState.jobId = result.jobId;
    pollForCompletion(result.jobId);

  } 
  catch (err) 
  {
    errorMsg.textContent = err.message || "Conversion failed";
    setState({ status: "error" });
  }
});

// POLL FOR CONVERSION COMPLETION
function pollForCompletion(jobId) 
{
  const pollInterval = setInterval(async () => {
    try {
      const response = await fetch(`progress.php?job=${jobId}`);
      const result = await response.json();
      
      if (result.status === "done") {
        // Conversion complete!
        downloadLink.href = result.url;
        setState({ status: "done" });
        clearInterval(pollInterval);
      } else if (result.status === "error") {
        // Error occurred
        errorMsg.textContent = result.message || "Conversion failed";
        setState({ status: "error" });
        clearInterval(pollInterval);
      }
      // If status is "processing", continue polling
      
    } catch (err) {
      console.error("Polling error:", err);
      errorMsg.textContent = "Failed to check conversion status";
      setState({ status: "error" });
      clearInterval(pollInterval);
    }
  }, 1000); // Poll every second
  
  // Stop polling after 60 seconds (timeout)
  setTimeout(() => {
    clearInterval(pollInterval);
    if (appState.status === "uploading") {
      errorMsg.textContent = "Conversion timed out";
      setState({ status: "error" });
    }
  }, 60000);
}
