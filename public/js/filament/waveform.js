document.addEventListener("livewire:init", () => {
    Livewire.on("generate-waveform", async (musicId, fileUrl) => {
        console.log("🎧 JS EVENT RECEIVED", {
            musicId,
            fileUrl,
        });

        try {
            const audioCtx = new AudioContext();

            const res = await fetch(fileUrl);

            console.log("📥 Fetch response:", res.status);

            const buffer = await res.arrayBuffer();

            const audioBuffer = await audioCtx.decodeAudioData(buffer);

            console.log("🎼 Audio decoded", audioBuffer);

            const raw = audioBuffer.getChannelData(0);

            console.log("📊 Raw audio length:", raw.length);

            const samples = 300;
            const blockSize = Math.floor(raw.length / samples);

            const canvas = document.createElement("canvas");
            canvas.width = 800;
            canvas.height = 120;

            const ctx = canvas.getContext("2d");

            ctx.fillStyle = "#fff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = "#000";

            for (let i = 0; i < samples; i++) {
                let sum = 0;
                let start = i * blockSize;

                for (let j = 0; j < blockSize; j++) {
                    sum += Math.abs(raw[start + j] || 0);
                }

                let avg = sum / blockSize;
                let height = avg * canvas.height;

                ctx.fillRect(i * 2, (canvas.height - height) / 2, 1, height);
            }

            const png = canvas.toDataURL("image/png");

            console.log("🖼 Waveform generated");

            const response = await fetch("/api/waveform", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    music_id: musicId,
                    image: png,
                }),
            });

            console.log("📤 Upload response:", response.status);
        } catch (error) {
            console.error("❌ Waveform error:", error);
        }
    });
});
